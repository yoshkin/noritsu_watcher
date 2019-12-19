<?php


namespace AYashenkov;

/*
 * Класс для имитации структуры данных Сишного компонента
 */
class LogMessage
{
    /* @var int */
    private $package;

    /* @var int */
    private $code;

    /* @var string */
    private $desc;

    /* @var string */
    private $comment;

    /* Шаблон, по которому данные будут упакованы в байтовую строку*/
    private $packTemplate = 'lxxxxA40A255';

    public function __construct(int $package, int $code, string $desc, string $comment)
    {
        $this->package = $package;
        $this->code = $code;
        $this->desc = $desc;
        $this->comment = $comment;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string|false
     */
    public function getLogMessageInBytes(): string
    {
        return pack($this->packTemplate, $this->package, $this->desc, $this->comment);
    }
}