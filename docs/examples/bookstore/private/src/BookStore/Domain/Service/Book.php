<?php
namespace BookStore\Domain\Service;

use BookStore\Domain\Entity\Book as BookEntity;

/**
 * @Component(name=bookDomainService)
 */
class Book extends AbstractService
{
    /**
     * @Resource
     * @Required
     * @var \BookStore\Domain\Repository\Book
     */
    protected $bookRepository;

    public function create($title, $description, $isbn)
    {
        $entity = new BookEntity($title, $isbn, $description);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function getByIsbn($isbn)
    {
        return $this->bookRepository->findOneByIsbn($isbn);
    }

    public function getAll()
    {
        return $this->bookRepository->findAll();
    }
}