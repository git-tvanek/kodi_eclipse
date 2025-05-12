<?php

declare(strict_types=1);

namespace App\Presentation\Tag;

use App\Presentation\BasePresenter;
use App\Facade\TagFacade;
use App\Facade\AddonFacade;
use App\Forms\Factory\TagFormFactory;
use Nette\Application\UI\Form;

class TagPresenter extends BasePresenter
{
    /** @var TagFacade */
    private TagFacade $tagFacade;
    
    /** @var AddonFacade */
    private AddonFacade $addonFacade;
    
    /** @var TagFormFactory */
    private TagFormFactory $tagFormFactory;
    
    /**
     * Constructor
     */
    public function __construct(
        TagFacade $tagFacade,
        AddonFacade $addonFacade,
        TagFormFactory $tagFormFactory
    ) {
        $this->tagFacade = $tagFacade;
        $this->addonFacade = $addonFacade;
        $this->tagFormFactory = $tagFormFactory;
    }
    
    /**
     * Default action - list all tags
     */
    public function renderDefault(int $page = 1): void
    {
        $itemsPerPage = 50;
        
        // Get all tags with counts
        $tagsWithCounts = $this->tagFacade->getTagsWithCounts();
        
        // Get trending tags
        $trendingTags = $this->tagFacade->getTrendingTags(30, 10);
        
        // Generate tag cloud
        $tagCloud = $this->tagFacade->generateTagCloud(100);
        
        $this->template->tagsWithCounts = $tagsWithCounts;
        $this->template->trendingTags = $trendingTags;
        $this->template->tagCloud = $tagCloud;
        $this->template->page = $page;
    }
    
    /**
     * Detail action - show addons with a specific tag
     */
    public function renderDetail(string $slug, int $page = 1): void
    {
        $itemsPerPage = 12;
        
        // Get tag by slug
        $tag = $this->tagFacade->getTagBySlug($slug);
        if (!$tag) {
            $this->error('Tag not found');
        }
        
        // Get addons with this tag
        $addons = $this->tagFacade->getAddonsByTag($tag->id, $page, $itemsPerPage);
        
        // Get related tags
        $relatedTags = $this->tagFacade->getRelatedTags($tag->id, 10);
        
        $this->template->tag = $tag;
        $this->template->addons = $addons;
        $this->template->relatedTags = $relatedTags;
        $this->template->page = $page;
    }
    
    /**
     * Add action - form for adding a new tag
     */
    public function renderAdd(): void
    {
        // Check if user is logged in (in a real app)
        // if (!$this->getUser()->isLoggedIn()) {
        //     $this->flashMessage('You must be logged in to add a tag', 'danger');
        //     $this->redirect('Sign:in');
        // }
        
        $this->template->title = 'Add New Tag';
    }
    
    /**
     * Edit action - form for editing a tag
     */
    public function renderEdit(int $id): void
    {
        // Check if user is logged in (in a real app)
        // if (!$this->getUser()->isLoggedIn()) {
        //     $this->flashMessage('You must be logged in to edit a tag', 'danger');
        //     $this->redirect('Sign:in');
        // }
        
        // Get tag by ID
        $tag = $this->tagFacade->getTag($id);
        if (!$tag) {
            $this->error('Tag not found');
        }
        
        $this->template->tag = $tag;
        $this->template->title = 'Edit Tag: ' . $tag->name;
        
        // Set form defaults
        $this['tagForm']->setDefaults($tag->toArray());
    }

    /**
 * Smaže tag
 *
 * @param int $id
 */
public function renderDelete(int $id): void
{
    // Check if user is logged in (in a real app)
    // if (!$this->getUser()->isLoggedIn()) {
    //     $this->flashMessage('Pro smazání tagu musíte být přihlášen', 'danger');
    //     $this->redirect('Sign:in');
    // }
    
    try {
        // Pokus o smazání tagu
        $result = $this->tagFacade->deleteTag($id);
        
        if ($result) {
            $this->flashMessage('Tag byl úspěšně smazán', 'success');
        } else {
            $this->flashMessage('Tag se nepodařilo smazat', 'danger');
        }
    } catch (\Exception $e) {
        $this->flashMessage('Chyba při mazání tagu: ' . $e->getMessage(), 'danger');
    }
    
    // Přesměrování zpět na seznam tagů
    $this->redirect('Tag:default');
}
    
    /**
     * Component factory for the tag form
     */
    protected function createComponentTagForm(): Form
    {
        $form = $this->tagFormFactory->create();
        
        // Set callback for form processing
        $form->onSuccess[] = function (array $data, $tag) {
            $this->processTagForm($data, $tag);
        };
        
        return $form->create();
    }
    
    /**
     * Process tag form
     */
    private function processTagForm(array $data, $tag): void
    {
        if (isset($data['id'])) {
            // Update existing tag
            $this->tagFacade->updateTag($data['id'], $data);
            $this->flashMessage('Tag updated successfully', 'success');
            
            // Get the updated tag
            $updatedTag = $this->tagFacade->getTag($data['id']);
            $this->redirect('Tag:detail', $updatedTag->slug);
        } else {
            // Create new tag
            $tagId = $this->tagFacade->createTag($data['name']);
            $this->flashMessage('Tag created successfully', 'success');
            
            // Get the created tag
            $newTag = $this->tagFacade->getTag($tagId);
            $this->redirect('Tag:detail', $newTag->slug);
        }
    }
}