<?php
namespace BookStore\Domain\Entity;

/**
 * @Entity
 */
class Book
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /**
     * @Column(type="string", unique="true", nullable="false", length="16")
     * @var string
     */
    protected $isbn;

    /**
     * @Column(type="text", nullable="false")
     * @var string
     */
    protected $title;

    /**
     * @Column(type="text", nullable="false")
     * @var string
     */
    protected $description;

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }
    public function getIsbn()
    {
        return $this->isbn;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function __construct($title, $isbn, $description)
    {
        $this->title = $title;
        $this->isbn = $isbn;
        $this->description = $description;
    }
}