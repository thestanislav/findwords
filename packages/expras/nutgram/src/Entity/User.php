<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 8/24/2017
 * Time: 15:00
 */

namespace ExprAs\Nutgram\Entity;

use Doctrine\DBAL\Types\Types as DoctrineTypes;
use ExprAs\Rest\Entity\AbstractEntity;
use ExprAs\Doctrine\Behavior\Timestampable\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use ExprAs\Rest\Mappings\Queryable;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ExprAs\Nutgram\Entity\UserMessage;
/**
 * Class TelegramUser
 */
#[ORM\MappedSuperclass]
class User
{
    use TimestampableTrait;

    /**
     * @var integer
     */
    #[ORM\Id]
    #[ORM\Column(type:  DoctrineTypes::BIGINT)]
    protected string $id;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_bot', type: 'boolean', nullable: false)]
    protected bool $isBot = false;

    /**
     * @var ?string
     */
    #[Queryable]
    #[ORM\Column(name: 'username', type: 'string', nullable: true)]
    protected ?string $username = null ;

    /**
     * @var ?string
     */
    #[Queryable]
    #[ORM\Column(name: 'phone_number', type: 'string', nullable: true)]
    protected ?string $phoneNumber = null;

    /**
     * @var string
     */
    #[Queryable]
    #[ORM\Column(name: 'first_name', type: 'string', nullable: true)]
    protected ?string $firstName = null;

    /**
     * @var ?string
     */
    #[Queryable]
    #[ORM\Column(name: 'last_name', type: 'string', nullable: true)]
    protected ?string $lastName = null;

    /**
     * @var ?string
     */
    #[ORM\Column(name: 'language_code', type: 'string', nullable: true, length: 4)]
    protected ?string $languageCode = null;


    /**
     * @var ?bool
     */
    #[ORM\Column(name: 'can_join_groups', type: 'boolean', nullable: true)]
    protected ?bool $canJoinGroups = null;

    /**
     * Optional. True, if privacy mode is disabled for the bot. Returned only in getMe.
     *
     * @var ?bool|null
     */
    #[ORM\Column(name: 'can_read_all_group_messages', type: 'boolean', nullable: true)]
    public ?bool $canReadAllGroupMessages = null;

    /**
     * Optional. True, if the bot supports inline queries. Returned only in getMe.
     *
     * @var ?bool|null
     */
    #[ORM\Column(name: 'supports_inline_queries', type: 'boolean', nullable: true)]
    public ?bool $supportsInlineQueries = null;



    /**
     * @var array
     */
    #[ORM\Column(name: 'params', type: 'array', nullable: true)]
    protected ?array $params = null;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: UserMessage::class, cascade: ["persist", "remove"])]
    protected Collection $messages;

    /**
     * The handler that should process the next text message
     * Format: "HandlerClass::methodName" or just "HandlerClass" for default method
     */
    #[ORM\Column(name: 'waiting_for_handler', type: 'string', nullable: true)]
    protected ?string $waitingForHandler = null;

    /**
     * Additional context data for the waiting handler
     */
    #[ORM\Column(name: 'waiting_context', type: 'json', nullable: true)]
    protected ?array $waitingContext = [];

    /**
     * Timestamp when the waiting state was set
     */
    #[ORM\Column(name: 'waiting_since', type: 'datetime', nullable: true)]
    protected ?\DateTime $waitingSince = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->waitingContext = [];
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function setMessages(Collection $messages): void
    {
        $this->messages = $messages;
    }

    public function addMessages($messages): void
    {
        if (is_iterable($messages)) {
            foreach ($messages as $message) {
                $this->addMessages($message);
            }
            return;
        }
        if (!$this->messages->contains($messages)) {
            $messages->setUser($this);
            $this->messages->add($messages);
        }
        return;
    }

    public function removeMessages($messages): void
    {
        if (is_iterable($messages)) {
            foreach ($messages as $message) {
                $this->removeMessages($message);
            }
            return;
        }
        $this->messages->removeElement($messages);
        return;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return ?string
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }




    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return ?string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }



    /**
     * @return ?string
     */
    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    public function setLanguageCode(?string $languageCode): void
    {
        $this->languageCode = $languageCode;
    }



    /**
     * @return bool
     */
    public function isBot(): bool
    {
        return $this->isBot;
    }

    public function setIsBot(bool $isBot): void
    {
        $this->isBot = $isBot;
    }

    /**
     * @return ?string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return bool|null
     */
    public function getCanJoinGroups(): ?bool
    {
        return $this->canJoinGroups;
    }

    /**
     * @param bool|null $canJoinGroups
     */
    public function setCanJoinGroups(?bool $canJoinGroups): void
    {
        $this->canJoinGroups = $canJoinGroups;
    }

    /**
     * @return bool|null
     */
    public function getCanReadAllGroupMessages(): ?bool
    {
        return $this->canReadAllGroupMessages;
    }

    /**
     * @param bool|null $canReadAllGroupMessages
     */
    public function setCanReadAllGroupMessages(?bool $canReadAllGroupMessages): void
    {
        $this->canReadAllGroupMessages = $canReadAllGroupMessages;
    }

    /**
     * @return bool|null
     */
    public function getSupportsInlineQueries(): ?bool
    {
        return $this->supportsInlineQueries;
    }

    /**
     * @param bool|null $supportsInlineQueries
     */
    public function setSupportsInlineQueries(?bool $supportsInlineQueries): void
    {
        $this->supportsInlineQueries = $supportsInlineQueries;
    }




    /**
     * @param  null $name
     * @return array|mixed|null
     */
    public function getParams($name = null)
    {
        return is_null($name) ? $this->params : ($this->params[$name] ?? null);
    }

    /**
     * @param array | mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
        return;
    }

    /**
     * @param array | mixed $params
     */
    public function addParams($params, $value = null)
    {
        if (is_iterable($params)) {
            foreach ($params as $_k => $_v) {
                $this->addParams($_k, $_v);
            }
            return;
        }
        $this->params[$params] = $value;
        return;
    }

    /**
     * @param string[] | string $keys
     */
    public function removeParams($keys)
    {
        if (is_iterable($keys)) {
            foreach ($keys as $_v) {
                $this->removeParams($_v);
            }
            return;
        }
        unset($this->params[$keys]);
        return;
    }

    // Waiting state methods

    /**
     * Get the handler that should process the next text message
     */
    public function getWaitingForHandler(): ?string
    {
        return $this->waitingForHandler;
    }

    /**
     * Set the handler that should process the next text message
     */
    public function setWaitingForHandler(?string $waitingForHandler): void
    {
        $this->waitingForHandler = $waitingForHandler;
    }

    /**
     * Get additional context data for the waiting handler
     */
    public function getWaitingContext(): array
    {
        return $this->waitingContext ?? [];
    }

    /**
     * Set additional context data for the waiting handler
     */
    public function setWaitingContext(?array $waitingContext): void
    {
        $this->waitingContext = $waitingContext ?? [];
    }

    /**
     * Get timestamp when the waiting state was set
     */
    public function getWaitingSince(): ?\DateTime
    {
        return $this->waitingSince;
    }

    /**
     * Set timestamp when the waiting state was set
     */
    public function setWaitingSince(?\DateTime $waitingSince): void
    {
        $this->waitingSince = $waitingSince;
    }

    /**
     * Check if user is waiting for input
     */
    public function isWaitingForInput(): bool
    {
        return $this->waitingForHandler !== null;
    }

    /**
     * Clear waiting state
     */
    public function clearWaitingState(): void
    {
        $this->waitingForHandler = null;
        $this->waitingContext = [];
        $this->waitingSince = null;
    }

    /**
     * Add context data
     */
    public function addWaitingContext(string $key, $value): void
    {
        if ($this->waitingContext === null) {
            $this->waitingContext = [];
        }
        $this->waitingContext[$key] = $value;
    }

    /**
     * Get specific context value
     */
    public function getWaitingContextValue(string $key, $default = null)
    {
        if ($this->waitingContext === null) {
            return $default;
        }
        return $this->waitingContext[$key] ?? $default;
    }
}
