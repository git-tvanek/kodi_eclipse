<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use Nette;
use App\Facade\AddonFacade;
use App\Facade\CategoryFacade;
use App\Facade\TagFacade;



final class HomePresenter extends Nette\Application\UI\Presenter
{
    /** @var AddonFacade */
    private AddonFacade $addonFacade;
    private CategoryFacade $categoryFacade;
    private TagFacade $tagFacade;

      /**
     * Constructor
     */
     /**
     * Constructor
     */
    public function __construct(
        AddonFacade $addonFacade,
        CategoryFacade $categoryFacade,
        TagFacade $tagFacade
    ) {
        $this->addonFacade = $addonFacade;
        $this->categoryFacade = $categoryFacade;
        $this->tagFacade = $tagFacade;
    }

     /**
     * Default action
     */
    public function renderDefault(): void
    {
        // Get popular, top-rated, and newest add-ons for homepage
        $this->template->popularAddons = $this->addonFacade->getPopularAddons(6);
        $this->template->topRatedAddons = $this->addonFacade->getTopRatedAddons(6);
        $this->template->newestAddons = $this->addonFacade->getNewestAddons(6);

        // Get categories for browsing
        $this->template->categories = $this->categoryFacade->getRootCategories();
        
        // Get popular tags
        $tagCloud = $this->tagFacade->generateTagCloud(20);
        $this->template->popularTags = $tagCloud;
    }
    
    /**
     * Addon list with sorting action
     */
    public function renderAddon(string $sort = 'name'): void
    {
        // Based on the sort parameter, get the appropriate addon collection
        if ($sort === 'rating') {
            $this->template->addons = $this->addonFacade->getTopRatedAddons(18);
            $this->template->sort = 'rating';
        } elseif ($sort === 'created_at') {
            $this->template->addons = $this->addonFacade->getNewestAddons(18);
            $this->template->sort = 'created_at';
        } else {
            $this->template->addons = $this->addonFacade->getPopularAddons(18);
            $this->template->sort = 'popular';
        }
    }
}
