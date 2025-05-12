<?php

declare(strict_types=1);

namespace App\Presentation\Author;

use App\Presentation\BasePresenter;
use App\Facade\AuthorFacade;
use App\Facade\AddonFacade;
use App\Forms\Factory\AuthorFormFactory;
use Nette\Application\UI\Form;
use Nette\Utils\Paginator;

class AuthorPresenter extends BasePresenter
{
    /** @var AuthorFacade */
    private AuthorFacade $authorFacade;
    
    /** @var AddonFacade */
    private AddonFacade $addonFacade;
    
    /** @var AuthorFormFactory */
    private AuthorFormFactory $authorFormFactory;
    
    /**
     * Constructor
     */
    public function __construct(
        AuthorFacade $authorFacade,
        AddonFacade $addonFacade,
        AuthorFormFactory $authorFormFactory
    ) {
        $this->authorFacade = $authorFacade;
        $this->addonFacade = $addonFacade;
        $this->authorFormFactory = $authorFormFactory;
    }
    
    /**
     * Default action - list all authors
     */
    public function renderDefault(int $page = 1): void
    {
        $itemsPerPage = 15;
        
        // Get all authors with pagination
        $authors = $this->authorFacade->findAuthors([], $page, $itemsPerPage);
        
        // Get top authors
        $topAuthors = $this->authorFacade->getTopAuthors('downloads', 5);
        
        $this->template->authors = $authors;
        $this->template->topAuthors = $topAuthors;
        $this->template->page = $page;
    }
    
    /**
     * Detail action - show a specific author
     */
    public function renderDetail(int $id, int $page = 1): void
    {
        $itemsPerPage = 10;
        
        // Get author with addons
        $authorWithAddons = $this->authorFacade->getAuthorWithAddons($id);
        if (!$authorWithAddons) {
            $this->error('Author not found');
        }
        
        // Get author statistics
        $authorStats = $this->authorFacade->getAuthorStatistics($id);
        
        // Get author collaboration network
        $collaborationNetwork = $this->authorFacade->getAuthorCollaborationNetwork($id, 2);
        
        $this->template->author = $authorWithAddons['author'];
        $this->template->addons = $authorWithAddons['addons'];
        $this->template->statistics = $authorStats;
        $this->template->collaborationNetwork = $collaborationNetwork;
        $this->template->page = $page;
    }
    
    /**
     * Add action - form for adding a new author
     */
    public function renderAdd(): void
    {
        // Check if user is logged in (in a real app)
        // if (!$this->getUser()->isLoggedIn()) {
        //     $this->flashMessage('You must be logged in to add an author', 'danger');
        //     $this->redirect('Sign:in');
        // }
        
        $this->template->title = 'Add New Author';
    }
    
    /**
     * Edit action - form for editing an author
     */
    public function renderEdit(int $id): void
    {
        // Check if user is logged in (in a real app)
        // if (!$this->getUser()->isLoggedIn()) {
        //     $this->flashMessage('You must be logged in to edit an author', 'danger');
        //     $this->redirect('Sign:in');
        // }
        
        // Get author by ID
        $author = $this->authorFacade->getAuthor($id);
        if (!$author) {
            $this->error('Author not found');
        }
        
        $this->template->author = $author;
        $this->template->title = 'Edit Author: ' . $author->name;
        
        // Set form defaults
        $this['authorForm']->setDefaults($author->toArray());
    }

    /**
     * Delete action - delete an author
     */
public function renderDelete(int $id): void
{
    // Check if user is logged in (in a real app)
    // if (!$this->getUser()->isLoggedIn()) {
    //     $this->flashMessage('You must be logged in to delete an author', 'danger');
    //     $this->redirect('Sign:in');
    // }
    
    try {
        // Delete author
        $result = $this->authorFacade->deleteAuthor($id);
        
        if ($result) {
            $this->flashMessage('Autor byl úspěšně smazán', 'success');
        } else {
            $this->flashMessage('Autora se nepodařilo smazat', 'danger');
        }
    } catch (\Exception $e) {
        $this->flashMessage('Chyba při mazání autora: ' . $e->getMessage(), 'danger');
    }
    
    // Redirect to author list
    $this->redirect('Author:default');
}
    
    /**
     * Component factory for the author form
     */
    protected function createComponentAuthorForm(): Form
    {
        $form = $this->authorFormFactory->create();
        
        // Set callback for form processing
        $form->onSuccess[] = function (array $data) {
            $this->processAuthorForm($data);
        };
        
        return $form->create();
    }
    
    /**
     * Process author form
     */
    private function processAuthorForm(array $data): void
    {
        if (isset($data['id'])) {
            // Update existing author
            $authorId = $this->authorFacade->updateAuthor($data['id'], $data);
            $this->flashMessage('Author updated successfully', 'success');
        } else {
            // Create new author
            $authorId = $this->authorFacade->createAuthor(
                $data['name'],
                $data['email'] ?? null,
                $data['website'] ?? null
            );
            $this->flashMessage('Author created successfully', 'success');
        }
        
        $this->redirect('Author:detail', $authorId);
    }
}