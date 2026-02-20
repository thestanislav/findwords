<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 21.08.13
 * Time: 19:18
 */

namespace ExprAs\Mailer\Entity;


use Doctrine\DBAL\Types\Types as DoctrineTypes;
use ExprAs\Rest\Entity\AbstractEntity;
use ExprAs\Doctrine\Behavior\Timestampable\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Mime\Email;


#[ORM\Table(name: 'expras_mail_queue')]
#[ORM\Index(columns: ["status"], name: 'status_idx')]
#[ORM\Entity]
class MailQueue extends AbstractEntity
{
    use TimestampableTrait;

    const STATUS_QUEUED = 'queued';
    const STATUS_SENT = 'sent';
    const STATUS_ERROR = 'error';

    #[ORM\Column(name: 'group_name', type: DoctrineTypes::STRING, length: 32, nullable: false, options: ["default" => "default"])]
    protected string $groupName = 'default';



    #[ORM\Column(name: "schedule_time", type: DoctrineTypes::DATETIME_MUTABLE, nullable: false)]
    protected \DateTime $scheduleTime;



    #[ORM\Column( type: DoctrineTypes::STRING, length:16, nullable: false, options: ['default' => 'waiting'])]
    protected string $status = self::STATUS_QUEUED;


    #[ORM\Column( name: "message", type: "MailMessage", nullable: false)]
    protected Email $message;

    /**
     * @var
     * @ORM\Column(name="last_error", type="string", nullable=true)
     */
    #[ORM\Column( name: "last_error", type: DoctrineTypes::STRING, length: 1024, nullable: true)]
    protected ?string $lastError;



    public function __construct()
    {
        $this->scheduleTime = new \DateTime();
    }

    public function getGroupName(): string
    {
        return $this->groupName;
    }

    public function setGroupName(string $groupName): void
    {
        $this->groupName = $groupName;
    }

    public function getScheduleTime(): \DateTime
    {
        return $this->scheduleTime;
    }

    public function setScheduleTime(\DateTime $scheduleTime): void
    {
        $this->scheduleTime = $scheduleTime;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getMessage(): Email
    {
        return $this->message;
    }

    public function setMessage(Email $message): void
    {
        $this->message = $message;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function setLastError(?string $lastError): void
    {
        $this->lastError = $lastError;
    }



}

