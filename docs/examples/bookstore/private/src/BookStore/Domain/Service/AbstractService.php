<?php
namespace BookStore\Domain\Service;

/**
 * @Component
 */
class AbstractService
{
    /**
     * @Resource
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
}