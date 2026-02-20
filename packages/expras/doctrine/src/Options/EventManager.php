<?php

namespace ExprAs\Doctrine\Options;
use Laminas\Stdlib\AbstractOptions;
use SplPriorityQueue;
/**
 * EventManager options
 *
 * @template-extends AbstractOptions<mixed>
 */
final class EventManager extends AbstractOptions
{
    /**
     * A priority queue of subscribers. The queue can contain the FQN of the
     * class to instantiate OR a string to be located with the
     * service locator.
     *
     * @var SplPriorityQueue<int, string>
     */
    protected ?SplPriorityQueue $subscribers = null;

    /** @param SplPriorityQueue<int, string> $subscribers */
    public function setSubscribers(array $subscribers): self
    {
        // Follows the PipelineAndRoutesDelegator pattern for priority queue insertion
        $this->subscribers = new SplPriorityQueue();
        $serial = PHP_INT_MAX;
        foreach ($subscribers as $priority => $subscriber) {
            // Allow array style (with specific priority), or direct
            if (is_array($subscriber) && isset($subscriber['priority'])) {
                $priority = $subscriber['priority'];
                $subscriber = $subscriber['subscriber'];
            } elseif (!is_int($priority)) {
                $priority = 1;
            }
            // Use (priority, serial) array to enforce FIFO for equal priorities
            $this->subscribers->insert($subscriber, [$priority, $serial]);
            $serial -= 1;
        }
        return $this;
    
    }

    /** @return ?SplPriorityQueue */
    public function getSubscribers():?SplPriorityQueue
    {
        return $this->subscribers ?? null;
    }
}
