<?php

namespace HiEvents\DomainObjects\Generated;

/**
 * THIS FILE IS AUTOGENERATED - DO NOT EDIT IT DIRECTLY.
 * @package HiEvents\DomainObjects\Generated
 */
abstract class QuestionAnswerDomainObjectAbstract extends \HiEvents\DomainObjects\AbstractDomainObject
{
    final public const SINGULAR_NAME = 'question_answer';
    final public const PLURAL_NAME = 'question_answers';
    final public const ID = 'id';
    final public const QUESTION_ID = 'question_id';
    final public const ORDER_ID = 'order_id';
    final public const ATTENDEE_ID = 'attendee_id';
    final public const TICKET_ID = 'ticket_id';
    final public const CREATED_AT = 'created_at';
    final public const UPDATED_AT = 'updated_at';
    final public const DELETED_AT = 'deleted_at';
    final public const ANSWER = 'answer';

    protected int $id;
    protected int $question_id;
    protected int $order_id;
    protected ?int $attendee_id = null;
    protected ?int $ticket_id = null;
    protected string $created_at;
    protected string $updated_at;
    protected ?string $deleted_at = null;
    protected array|string|null $answer = null;

    public function toArray(): array
    {
        return [
                    'id' => $this->id ?? null,
                    'question_id' => $this->question_id ?? null,
                    'order_id' => $this->order_id ?? null,
                    'attendee_id' => $this->attendee_id ?? null,
                    'ticket_id' => $this->ticket_id ?? null,
                    'created_at' => $this->created_at ?? null,
                    'updated_at' => $this->updated_at ?? null,
                    'deleted_at' => $this->deleted_at ?? null,
                    'answer' => $this->answer ?? null,
                ];
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setQuestionId(int $question_id): self
    {
        $this->question_id = $question_id;
        return $this;
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function setOrderId(int $order_id): self
    {
        $this->order_id = $order_id;
        return $this;
    }

    public function getOrderId(): int
    {
        return $this->order_id;
    }

    public function setAttendeeId(?int $attendee_id): self
    {
        $this->attendee_id = $attendee_id;
        return $this;
    }

    public function getAttendeeId(): ?int
    {
        return $this->attendee_id;
    }

    public function setTicketId(?int $ticket_id): self
    {
        $this->ticket_id = $ticket_id;
        return $this;
    }

    public function getTicketId(): ?int
    {
        return $this->ticket_id;
    }

    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setUpdatedAt(string $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    public function setDeletedAt(?string $deleted_at): self
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deleted_at;
    }

    public function setAnswer(array|string|null $answer): self
    {
        $this->answer = $answer;
        return $this;
    }

    public function getAnswer(): array|string|null
    {
        return $this->answer;
    }
}