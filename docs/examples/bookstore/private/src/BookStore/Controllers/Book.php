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

    private function _validateBookForm($title, $description, $isbn)
    {
        $errors = array();
        if (empty($title)) {
            $errors['title'] = 'cant_be_empty';
        }
        if (empty($description)) {
            $errors['description'] = 'cant_be_empty';
        }
        if (empty($isbn)) {
            $errors['isbn'] = 'cant_be_empty';
        }
        if (empty($errors)) {
            if ($this->bookDomainService->getByIsbn($isbn) !== null) {
                $errors['isbn'] = 'already_exists';
            }
        }
        return $errors;
    }

    public function doCreateAction($title, $description, $isbn)
    {
        $arguments = array(
        	'title' => $title, 'description' => $description, 'isbn' => $isbn
       	);
        $arguments['errors'] = $this->_validateBookForm($title, $description, $isbn);
        if (empty($arguments['errors'])) {
            try
            {
                $this->bookDomainService->create($title, $description, $isbn);
                return new RedirectModelAndView('/Book/created', $arguments);
            } catch (\Exception $exception) {
                $arguments['errors']['system'] = $exception->getMessage();
            }
        }
        return new ModelAndView('createBook', $arguments);
    }

    public function createdAction($title, $description, $isbn)
    {
        return new ModelAndView('created', array(
            'title' => $title, 'description' => $description, 'isbn' => $isbn
        ));
    }

    public function listAction()
    {
        $books = array();
        foreach ($this->bookDomainService->getAll() as $book) {
            $books[] = array(
                'title' => $book->getTitle(),
            	'isbn' => $book->getIsbn(),
            	'description' => $book->getDescription()
            );
        }
        return new ModelAndView('list', $books);
    }

    public function createAction()
    {
        $modelAndView = new ModelAndView('createBook');
        return $modelAndView;
    }

    public function __construct()
    {
    }
}