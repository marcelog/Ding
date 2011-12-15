<?php
namespace BookStore\Domain\Service;

/**
 * @Component
 */
class AbstractService
{
    /**
     * @Resource
     * @Required
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
}