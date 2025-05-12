<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\eclipse\app\Presentation\Home/default.latte */
final class Template_f83226472c extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\eclipse\\app\\Presentation\\Home/default.latte';

	public const Blocks = [
		['title' => 'blockTitle', 'content' => 'blockContent'],
	];


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		$this->renderBlock('title', get_defined_vars()) /* line 1 */;
		echo '

';
		$this->renderBlock('content', get_defined_vars()) /* line 3 */;
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['addon' => '26, 50, 84', 'category' => '110', 'tag' => '134'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block title} on line 1 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Domů';
	}


	/** {block content} on line 3 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="jumbotron bg-light p-5 rounded">
    <h1 class="display-15">xAddons.cz</h1>
    <p class="lead">Objevujte, stahujte a sdílejte nejlepší doplňky pro Kodi media center.</p>
    <hr class="my-4">
    <p>Procházejte naši rozsáhlou sbírku doplňků organizovanou podle kategorií, autorů nebo tagů.</p>
    <a class="btn btn-primary btn-lg" href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:')) /* line 9 */;
		echo '">
        <i class="fas fa-cube me-1"></i> Procházet doplňky
    </a>
    <a class="btn btn-outline-primary btn-lg ms-2" href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Search:advanced')) /* line 12 */;
		echo '">
        <i class="fas fa-search me-1"></i> Pokročilé vyhledávání
    </a>
</div>

<div class="row mt-5">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-award me-1"></i> Populární doplňky</h5>
            </div>
            <div class="card-body">
';
		if (isset($popularAddons) && $popularAddons->count() > 0) /* line 24 */ {
			echo '                    <div class="list-group">
';
			foreach ($popularAddons as $addon) /* line 26 */ {
				echo '                        <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:detail', [$addon->slug])) /* line 26 */;
				echo '" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            ';
				echo LR\Filters::escapeHtmlText($addon->name) /* line 28 */;
				echo '
                            <span class="badge bg-primary rounded-pill">';
				echo LR\Filters::escapeHtmlText($addon->downloads_count) /* line 29 */;
				echo '</span>
                        </a>
';

			}

			echo '                    </div>
';
		} else /* line 32 */ {
			echo '                    <p class="text-muted">Nebyly nalezeny žádné populární doplňky.</p>
';
		}
		echo '            </div>
            <div class="card-footer">
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:')) /* line 37 */;
		echo '" class="btn btn-sm btn-primary w-100">Zobrazit všechny doplňky</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-star me-1"></i> Nejlépe hodnocené doplňky</h5>
            </div>
            <div class="card-body">
';
		if (isset($topRatedAddons) && $topRatedAddons->count() > 0) /* line 48 */ {
			echo '                    <div class="list-group">
';
			foreach ($topRatedAddons as $addon) /* line 50 */ {
				echo '                        <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:detail', [$addon->slug])) /* line 50 */;
				echo '" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            ';
				echo LR\Filters::escapeHtmlText($addon->name) /* line 52 */;
				echo '
                            <div>
';
				for ($i = 1;
				$i <= 5;
				$i++) /* line 54 */ {
					if ($i <= $addon->rating) /* line 55 */ {
						echo '                                        <i class="fas fa-star text-warning"></i>
';
					} elseif ($i <= $addon->rating + 0.5) /* line 57 */ {
						echo '                                        <i class="fas fa-star-half-alt text-warning"></i>
';
					} else /* line 59 */ {
						echo '                                        <i class="far fa-star text-warning"></i>
';
					}


				}
				echo '                            </div>
                        </a>
';

			}

			echo '                    </div>
';
		} else /* line 66 */ {
			echo '                    <p class="text-muted">Nebyly nalezeny žádné hodnocené doplňky.</p>
';
		}
		echo '            </div>
            <div class="card-footer">
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon', ['sort' => 'rating'])) /* line 71 */;
		echo '" class="btn btn-sm btn-success w-100">Zobrazit nejlépe hodnocené</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-clock me-1"></i> Nejnovější doplňky</h5>
            </div>
            <div class="card-body">
';
		if (isset($newestAddons) && $newestAddons->count() > 0) /* line 82 */ {
			echo '                    <div class="list-group">
';
			foreach ($newestAddons as $addon) /* line 84 */ {
				echo '                        <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:detail', [$addon->slug])) /* line 84 */;
				echo '" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            ';
				echo LR\Filters::escapeHtmlText($addon->name) /* line 86 */;
				echo '
                            <small class="text-muted">';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($addon->created_at, 'j.n.Y')) /* line 87 */;
				echo '</small>
                        </a>
';

			}

			echo '                    </div>
';
		} else /* line 90 */ {
			echo '                    <p class="text-muted">Nebyly nalezeny žádné nové doplňky.</p>
';
		}
		echo '            </div>
            <div class="card-footer">
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon', ['sort' => 'created_at'])) /* line 95 */;
		echo '" class="btn btn-sm btn-info w-100">Zobrazit nejnovější</a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-folder me-1"></i> Procházet podle kategorií</h5>
            </div>
            <div class="card-body">
';
		if (isset($categories) && $categories->count() > 0) /* line 108 */ {
			echo '                    <div class="row">
';
			foreach ($categories as $category) /* line 110 */ {
				echo '                        <div class="col-md-4 mb-3">
                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Category:detail', [$category->slug])) /* line 111 */;
				echo '" class="text-decoration-none">
                                <div class="d-flex align-items-center p-2 border rounded hover-shadow">
                                    <i class="fas fa-folder me-2 text-primary"></i>
                                    <span>';
				echo LR\Filters::escapeHtmlText($category->name) /* line 114 */;
				echo '</span>
                                </div>
                            </a>
                        </div>
';

			}

			echo '                    </div>
';
		} else /* line 119 */ {
			echo '                    <p class="text-muted">Nebyly nalezeny žádné kategorie.</p>
';
		}
		echo '            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-tags me-1"></i> Populární tagy</h5>
            </div>
            <div class="card-body">
';
		if (isset($popularTags) && count($popularTags) > 0) /* line 132 */ {
			echo '                    <div class="d-flex flex-wrap gap-2">
';
			foreach ($popularTags as $tag) /* line 134 */ {
				echo '                        <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tag:detail', [$tag['slug']])) /* line 134 */;
				echo '" 
                           class="btn btn-sm btn-outline-secondary">
                            ';
				echo LR\Filters::escapeHtmlText($tag['name']) /* line 136 */;
				echo '
                        </a>
';

			}

			echo '                    </div>
';
		} else /* line 139 */ {
			echo '                    <p class="text-muted">Nebyly nalezeny žádné tagy.</p>
';
		}
		echo '            </div>
        </div>
    </div>
</div>
';
	}
}
