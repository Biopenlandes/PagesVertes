<?php

/**
 * This file is part of the MonVoisinFaitDuBio project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2016 Sebastian Castro - 90scastro@gmail.com
 * @license    MIT License
 * @Last Modified time: 2017-08-21 18:48:50
 */
 

namespace Biopen\GeoDirectoryBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Biopen\GeoDirectoryBundle\Document\ElementStatus;
use Biopen\GeoDirectoryBundle\Document\ModerationState;
use Biopen\GeoDirectoryBundle\Document\UserInteraction;
use Symfony\Component\Security\Core\SecurityContext;
use Biopen\CoreBundle\Services\ConfigurationService;

class ElementVoteService
{	
	protected $em;
    protected $user;
    protected $confService;

	/**
     * Constructor
     */
    public function __construct(DocumentManager $documentManager, SecurityContext $securityContext, ConfigurationService $confService)
    {
    	 $this->em = $documentManager;
         $this->user = $securityContext->getToken() ? $securityContext->getToken()->getUser() : null;
         $this->confService = $confService; 
         $this->securityContext = $securityContext;
    }

    public function voteForelement($element, $voteValue, $comment, $userMail = null)
    {
        // Check user don't vote for his own creation
        if ($element->getContributorMail() == $this->user->getEmail())
                return "Erreur : vous ne pouvez pas votez pour votre propre contribution";

        // CHECK USER HASN'T ALREADY VOTED
        $currentVotes = $element->getVotes();
        $hasAlreadyVoted = false;
        // if user is anonymous no need to check
        if (!$this->securityContext->isGranted('IS_AUTHENTICATED_ANONYMOUSLY'))
        {
            foreach ($currentVotes as $key => $vote) 
            {
                if ($vote->getUserMail() == $this->user->getEmail()) 
                {
                    $hasAlreadyVoted = true;
                    $oldVote= $vote;
                }
            }
        }

        $oldStatus = $element->getStatus();

        if (!$hasAlreadyVoted) $vote = new UserInteraction();       
        
        $vote->setValue($voteValue);
        $vote->setUserMail($userMail ? $userMail : $this->user ? $this->user->getEmail() : 'Anonymous');

        if ($comment) $vote->setComment($comment);

        $element->addVote($vote);

        if ($this->confService->isUserAllowed('directModeration'))
        {
            $procedureCompleteMessage = $this->handleVoteProcedureComplete($element, 'direct', $voteValue == 1);
        }
        else $procedureCompleteMessage = $this->checkVotes($element);

        $this->em->persist($element);
        $this->em->flush();

        $resultMessage = $hasAlreadyVoted ? 'Merci ' . $this->user . ' : votre vote a bien été modifié !' : 'Merci de votre contribution !';
        if ($procedureCompleteMessage) $resultMessage .= '</br>' . $procedureCompleteMessage;

        return $resultMessage;
    }

    public function checkVotes($element)
    {
        $currentVotes = $element->getVotes();
        $nbrePositiveVote = 0;
        $nbreNegativeVote = 0;

        foreach ($currentVotes as $key => $vote) 
        {
           $vote->getValue() >= 0 ? $nbrePositiveVote++ : $nbreNegativeVote++;
        }

        if ($nbrePositiveVote >= $this->confService->getConfig()->getMinVoteToChangeStatus())
        {
            if ($nbreNegativeVote <= $this->confService->getConfig()->getMaxOppositeVoteTolerated()) return $this->handleVoteProcedureComplete($element, 'collaborative', true);
            else 
            {
                $element->setModerationState(ModerationState::VotesConflicts);
            }
        }
        else if ($nbreNegativeVote >= $this->confService->getConfig()->getMinVoteToChangeStatus())
        {
            if ($nbrePositiveVote <= $this->confService->getConfig()->getMaxOppositeVoteTolerated()) return $this->handleVoteProcedureComplete($element, 'collaborative', false);
            else 
            {
                $element->setModerationState(ModerationState::VotesConflicts);
            }
        }
        else if ($daysFromContribution > $this->confService->getConfig()->getMaxDaysLeavingAnElementPending())
        {
            $element->setModerationState(ModerationState::PendingForTooLong);
        }
    }

    private function handleVoteProcedureComplete($element, $voteType, $voteValue)
    {        
        // dump("HandleVoteProcedureComplete");
        // dump("vote type " . $voteType . " // Vote value " . $voteValue);
        // dump($element);

        $message = '';

        // days from contribution
        $diffDate = time() - $element->getStatusChangedAt()->getTimestamp();
        $daysFromContribution = floor( $diffDate / (60 * 60 * 24));

        $elDisplayName = $this->confService->getConfig()->getElementDisplayNameDefinite();

        // we wait at least some days to validate collaboratively a contribution
        if ($voteType == 'direct' || $daysFromContribution >= $this->confService->getConfig()->getMinDayBetweenContributionAndCollaborativeValidation())
        {
            if ($element->getStatus() == ElementStatus::PendingAdd)
            {
                if ($voteType == 'collaborative') 
                {
                    $element->setStatus($voteValue ? ElementStatus::CollaborativeValidate : ElementStatus::CollaborativeRefused);
                    $message = $voteValue ? "Félicitations, " . $elDisplayName . " a reçu assez de vote pour être validé !" 
                                          : $elDisplayName . " a reçu suffisamment de votes négatifs, il va être supprimé.";
                                 
                }
                else if ($voteType == 'direct')    
                {
                    $element->setStatus($voteValue ? ElementStatus::AdminValidate : ElementStatus::AdminRefused);
                    $message = $voteValue ? $elDisplayName . " a bien été validé" : $elDisplayName . " a bien été refusé";
                }
            }
            else if ($element->getStatus() == ElementStatus::PendingModification)
            {            
                // if we validate modifications
                if ($voteValue)
                {
                    $modifiedElement = $element->getModifiedElement();
                   
                    if ($modifiedElement)
                    {
                        foreach ($element as $key => $value) 
                        {
                           if (!in_array($key, ["id", "status"])) $element->$key = $modifiedElement->$key;
                        }
                        // optionValue is pruivate so it's not in element $keys
                        $element->setOptionValues($modifiedElement->getOptionValues());
                        $element->setModifiedElement(null);
                    }
                    
                    $element->setStatus($voteType == 'direct' ? ElementStatus::AdminValidate : ElementStatus::CollaborativeValidate);
                    $message = $voteType == 'direct' ? "Les modifications ont bien été acceptées" : "Félicitations, les modifications ont reçues assez de vote pour être validées !";
                }
                // if modification are refused
                else
                {
                    $element->setModifiedElement(null);
                    $element->setStatus($voteType == 'direct' ? ElementStatus::AdminValidate : ElementStatus::CollaborativeValidate);
                    $message = $voteType == 'direct' ? "Les modifications ont bien été refusées" : "La proposition de modification a reçu suffisamment de votes négatifs, elle est annulée.";
                }            
            }
        }

        return $message;
    }
}
