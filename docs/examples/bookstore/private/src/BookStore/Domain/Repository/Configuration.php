<?php
namespace BookStore\Domain\Repository;

/**
 * @Configuration
 */
class Configuration
{
    /**
     * @Resource
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    protected function getRepository($name)
    {
        return $this->entityManager->getRepository("\BookStore\Domain\Entity\\$name");
    }

    /** @Bean */
    public function bookRepository()
    {
        return $this->getRepository('Book');
    }
}