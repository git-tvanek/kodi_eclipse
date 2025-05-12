<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\eclipse\app\Presentation\Addon/default.latte */
final class Template_0936c268d2 extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\eclipse\\app\\Presentation\\Addon/default.latte';

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
			foreach (array_intersect_key(['category' => '23', 'addon' => '78'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block title} on line 1 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Procházet doplňky';
	}


	/** {block content} on line 3 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-cube me-2"></i>Procházet doplňky</h1>
    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:add')) /* line 6 */;
		echo '" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Přidat nový doplněk
    </a>
</div>

<div class="row">
    <!-- Filters Sidebar -->
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Možnosti filtrování</h5>
            </div>
            <div class="card-body">
                <form action="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Search:advanced')) /* line 19 */;
		echo '" method="get">
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategorie</label>
                        <select class="form-select form-select-sm" id="category" name="category_ids[]" multiple>
';
		foreach ($categories as $category) /* line 23 */ {
			echo '                            <option value="';
			echo LR\Filters::escapeHtmlAttr($category->id) /* line 23 */;
			echo '" 
                                    ';
			if (isset($filters['category_ids']) && in_array($category->id, $filters['category_ids'])) /* line 24 */ {
				echo 'selected';
			}
			echo '>
                                ';
			echo LR\Filters::escapeHtmlText($category->name) /* line 25 */;
			echo '
                            </option>
';

		}

		echo '                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="minRating" class="form-label">Minimální hodnocení</label>
                        <select class="form-select form-select-sm" id="minRating" name="min_rating">
                            <option value="">Jakékoliv hodnocení</option>
                            <option value="5" ';
		if (isset($filters['min_rating']) && $filters['min_rating'] == 5) /* line 34 */ {
			echo 'selected';
		}
		echo '>5 hvězdiček</option>
                            <option value="4" ';
		if (isset($filters['min_rating']) && $filters['min_rating'] == 4) /* line 35 */ {
			echo 'selected';
		}
		echo '>4+ hvězdičky</option>
                            <option value="3" ';
		if (isset($filters['min_rating']) && $filters['min_rating'] == 3) /* line 36 */ {
			echo 'selected';
		}
		echo '>3+ hvězdičky</option>
                            <option value="2" ';
		if (isset($filters['min_rating']) && $filters['min_rating'] == 2) /* line 37 */ {
			echo 'selected';
		}
		echo '>2+ hvězdičky</option>
                            <option value="1" ';
		if (isset($filters['min_rating']) && $filters['min_rating'] == 1) /* line 38 */ {
			echo 'selected';
		}
		echo '>1+ hvězdička</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="kodiVersion" class="form-label">Verze Kodi</label>
                        <input type="text" class="form-control form-control-sm" id="kodiVersion" name="kodi_version" 
                               placeholder="např. 19.4" value="';
		echo LR\Filters::escapeHtmlAttr($filters['kodi_version'] ?? '') /* line 45 */;
		echo '">
                    </div>
                    
                    <div class="mb-3">
                        <label for="sortBy" class="form-label">Řadit podle</label>
                        <select class="form-select form-select-sm" id="sortBy" name="sort_by">
                            <option value="name" ';
		if (isset($filters['sort_by']) && $filters['sort_by'] == 'name') /* line 51 */ {
			echo 'selected';
		}
		echo '>Názvu</option>
                            <option value="downloads_count" ';
		if (isset($filters['sort_by']) && $filters['sort_by'] == 'downloads_count') /* line 52 */ {
			echo 'selected';
		}
		echo '>Stažení</option>
                            <option value="rating" ';
		if (isset($filters['sort_by']) && $filters['sort_by'] == 'rating') /* line 53 */ {
			echo 'selected';
		}
		echo '>Hodnocení</option>
                            <option value="created_at" ';
		if (isset($filters['sort_by']) && $filters['sort_by'] == 'created_at') /* line 54 */ {
			echo 'selected';
		}
		echo '>Data přidání</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="sortDir" class="form-label">Směr řazení</label>
                        <select class="form-select form-select-sm" id="sortDir" name="sort_dir">
                            <option value="ASC" ';
		if (isset($filters['sort_dir']) && $filters['sort_dir'] == 'ASC') /* line 61 */ {
			echo 'selected';
		}
		echo '>Vzestupně</option>
                            <option value="DESC" ';
		if (isset($filters['sort_dir']) && $filters['sort_dir'] == 'DESC') /* line 62 */ {
			echo 'selected';
		}
		echo '>Sestupně</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i> Použít filtry
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Addon List -->
    <div class="col-md-9">
';
		if (isset($addons) && $addons->getItems()->count() > 0) /* line 76 */ {
			echo '            <div class="row row-cols-1 row-cols-md-3 g-4">
';
			foreach ($addons->getItems() as $addon) /* line 78 */ {
				echo '                <div class="col">
                    <div class="card h-100 hover-shadow">
                        <div class="card-img-top position-relative" style="height: 150px; background-color: #f8f9fa;">
';
				if ($addon->icon_url) /* line 81 */ {
					echo '                                <img src="';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 82 */;
					echo '/uploads/';
					echo LR\Filters::escapeHtmlAttr($addon->icon_url) /* line 82 */;
					echo '" class="img-fluid p-3" alt="';
					echo LR\Filters::escapeHtmlAttr($addon->name) /* line 82 */;
					echo '" style="max-height: 100%;">
';
				} else /* line 83 */ {
					echo '                                <div class="d-flex justify-content-center align-items-center h-100">
                                    <i class="fas fa-cube fa-3x text-secondary"></i>
                                </div>
';
				}
				echo '                            <div class="position-absolute top-0 end-0 p-2">
                                <span class="badge bg-primary rounded-pill">
                                    <i class="fas fa-download me-1"></i> ';
				echo LR\Filters::escapeHtmlText($addon->downloads_count) /* line 90 */;
				echo '
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:detail', [$addon->slug])) /* line 97 */;
				echo '" class="text-decoration-none">';
				echo LR\Filters::escapeHtmlText($addon->name) /* line 97 */;
				echo '</a>
                            </h5>
                            
                            <div class="mb-2">
';
				for ($i = 1;
				$i <= 5;
				$i++) /* line 101 */ {
					if ($i <= $addon->rating) /* line 102 */ {
						echo '                                        <i class="fas fa-star text-warning"></i>
';
					} elseif ($i <= $addon->rating + 0.5) /* line 104 */ {
						echo '                                        <i class="fas fa-star-half-alt text-warning"></i>
';
					} else /* line 106 */ {
						echo '                                        <i class="far fa-star text-warning"></i>
';
					}


				}
				echo '                                <small class="text-muted ms-1">(';
				echo LR\Filters::escapeHtmlText($addon->rating) /* line 110 */;
				echo ')</small>
                            </div>
                            
                            <p class="card-text small text-muted">
';
				if ($addon->description) /* line 114 */ {
					echo '                                    ';
					echo LR\Filters::escapeHtmlText(($this->filters->truncate)($addon->description, 100)) /* line 115 */;
					echo "\n";
				} else /* line 116 */ {
					echo '                                    Žádný popis není k dispozici.
';
				}
				echo '                            </p>
                        </div>
                        
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Verze: ';
				echo LR\Filters::escapeHtmlText($addon->version) /* line 124 */;
				echo '</small>
                                <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:download', [$addon->slug])) /* line 125 */;
				echo '" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i> Stáhnout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
';

			}

			echo '            </div>
            
            <!-- Pagination -->
';
			if ($addons->getPages() > 1) /* line 135 */ {
				echo '                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item ';
				if (!$addons->hasPreviousPage()) /* line 138 */ {
					echo 'disabled';
				}
				echo '">
                            <a class="page-link" href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this', ['page' => $addons->getPreviousPage()])) /* line 139 */;
				echo '">&laquo; Předchozí</a>
                        </li>
                        
';
				for ($i = 1;
				$i <= $addons->getPages();
				$i++) /* line 142 */ {
					echo '                            <li class="page-item ';
					if ($i == $page) /* line 143 */ {
						echo 'active';
					}
					echo '">
                                <a class="page-link" href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this', ['page' => $i])) /* line 144 */;
					echo '">';
					echo LR\Filters::escapeHtmlText($i) /* line 144 */;
					echo '</a>
                            </li>
';

				}
				echo '                        
                        <li class="page-item ';
				if (!$addons->hasNextPage()) /* line 148 */ {
					echo 'disabled';
				}
				echo '">
                            <a class="page-link" href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this', ['page' => $addons->getNextPage()])) /* line 149 */;
				echo '">Další &raquo;</a>
                        </li>
                    </ul>
                </nav>
';
			}
		} else /* line 154 */ {
			echo '            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i> Nebyly nalezeny žádné doplňky. Zkuste upravit filtry nebo <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:add')) /* line 156 */;
			echo '" class="alert-link">přidat nový doplněk</a>.
            </div>
';
		}
		echo '    </div>
</div>
';
	}
}
