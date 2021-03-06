<?php

namespace TestApp\Events;

use Illuminate\Http\Request;
use LaravelCode\Middleware\Events\CrudEvent;

class BaseBlogEvent extends CrudEvent
{
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $description;

    /**
     * AccountUpdate constructor.
     *
     * @param $id
     * @param string $model
     * @param string $title
     * @param string $description
     */
    public function __construct($id, string $model, string $title, string $description)
    {
        parent::__construct($id, $model);
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * @param $id
     * @param string $model
     * @param array $payload
     * @return BaseBlogEvent
     */
    public static function fromPayload($id, string $model, array $payload)
    {
        return new static(
            null,
            $model,
            $payload['title'],
            $payload['description']
        );
    }

    /**
     * @param Request $request
     * @return array
     */
    public static function rules(Request $request): array
    {
        return [
            'title' => 'required',
            'description' => 'required',
        ];
    }

    /**
     * @return array
     */
    public function toPayload(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
        ];
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
