<?php
namespace BookStore\Controllers;

use Ding\Mvc\RedirectModelAndView;
use Ding\Mvc\ModelAndView;
use Ding\Mvc\ForwardModelAndView;
use Ding\MessageSource\IMessageSourceAware;

/**
 * @Component(name=bookController)
 * @Controller
 * @RequestMapping(url=/Book)
 */
class Book
{
    /**
     * @Required
     * @Resource
     * @var \BookStore\Domain\Service
     */
    protected $bookDomainService;

    private function _validateBookForm($name, $isbn, $errors = array())
    {
        $errors = array();
        if (empty($errors)) {
            if ($this->bookDomainService->getByIsbn($isbn) !== null) {
                $errors['name'] = 'already_exists';
            }
        }

    }
    public function doCreateAction($name, $isbn, array $errors = array())
    {
        $arguments = array(
			'name' => $name, 'isbn' => $isbn, 'errors' => $errors
        );
        if (empty($errors)) {
            if ($this->bookDomainService->getByIsbn($isbn) !== null) {
                $arguments['errors']['name'] = 'already_exists';
            }
        }
        return new ForwardModelAndView('/Book/create', $arguments);
    }
    public function createAction($name = '', $isbn = '', array $errors = array())
    {
        $modelAndView = new ModelAndView('createBook');
        return $modelAndView;
    }
    public function __construct()
    {
    }
}