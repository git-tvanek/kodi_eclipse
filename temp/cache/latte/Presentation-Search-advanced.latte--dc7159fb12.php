<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\eclipse\app\Presentation\Search/advanced.latte */
final class Template_dc7159fb12 extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\eclipse\\app\\Presentation\\Search/advanced.latte';

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
			foreach (array_intersect_key(['addon' => '59'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block title} on line 1 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Pokročilé vyhledávání';
	}


	/** {block content} on line 3 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="mb-4">
    <h1>Pokročilé vyhledávání</h1>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-filter me-1"></i> Vyhledávací filtry</h5>
            </div>
            <div class="card-body">
';
		$ʟ_tmp = $this->global->uiControl->getComponent('searchForm');
		if ($ʟ_tmp instanceof Nette\Application\UI\Renderable) $ʟ_tmp->redrawControl(null, false);
		$ʟ_tmp->render() /* line 15 */;

		echo '            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Search Results -->
';
		if (isset($results['addons']) && $results['addons']->getItems()->count() > 0) /* line 22 */ {
			echo '            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Výsledky vyhledávání (';
			echo LR\Filters::escapeHtmlText($results['addons']->getTotalCount()) /* line 25 */;
			echo ')</h5>
                    <span class="badge bg-primary">';
			echo LR\Filters::escapeHtmlText($results['addons']->getPage()) /* line 26 */;
			echo ' / ';
			echo LR\Filters::escapeHtmlText($results['addons']->getPages()) /* line 26 */;
			echo '</span>
                </div>
                <div class="card-body">
';
			if ($query) /* line 29 */ {
				echo '                        <div class="alert alert-info mb-4">
                            <i class="fas fa-search me-1"></i> Výsledky vyhledávání pro: <strong>';
				echo LR\Filters::escapeHtmlText($query) /* line 31 */;
				echo '</strong>
                            
';
				if (isset($filters) && count($filters) > 0) /* line 33 */ {
					echo '                                <div class="mt-2">
                                    <strong>Použité filtry:</strong>
                                    <ul class="mb-0">
';
					if (isset($filters['category_ids'])) /* line 37 */ {
						echo '                                            <li>Kategorie: ';
						echo LR\Filters::escapeHtmlText(implode(', ', $filters['category_ids'])) /* line 38 */;
						echo '</li>
';
					}
					if (isset($filters['tag_ids'])) /* line 40 */ {
						echo '                                            <li>Tagy: ';
						echo LR\Filters::escapeHtmlText(implode(', ', $filters['tag_ids'])) /* line 41 */;
						echo '</li>
';
					}
					if (isset($filters['min_rating'])) /* line 43 */ {
						echo '                                            <li>Minimální hodnocení: ';
						echo LR\Filters::escapeHtmlText($filters['min_rating']) /* line 44 */;
						echo '</li>
';
					}
					if (isset($filters['kodi_version'])) /* line 46 */ {
						echo '                                            <li>Verze Kodi: ';
						echo LR\Filters::escapeHtmlText($filters['kodi_version']) /* line 47 */;
						echo '</li>
';
					}
					if (isset($filters['sort_by']) && isset($filters['sort_dir'])) /* line 49 */ {
						echo '                                            <li>Řazení: ';
						echo LR\Filters::escapeHtmlText($filters['sort_by']) /* line 50 */;
						echo ' ';
						echo LR\Filters::escapeHtmlText($filters['sort_dir']) /* line 50 */;
						echo '</li>
';
					}
					echo '                                    </ul>
                                </div>
';
				}
				echo '                        </div>
';
			}
			echo '                    
                    <div class="list-group">
';
			foreach ($results['addons']->getItems() as $addon) /* line 59 */ {
				echo '                            <div class="list-group-item">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div style="width: 80px; height: 80px;">
';
				if ($addon->icon_url) /* line 64 */ {
					echo '                                                <img src="';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 65 */;
					echo '/uploads/';
					echo LR\Filters::escapeHtmlAttr($addon->icon_url) /* line 65 */;
					echo '" class="img-fluid rounded" alt="';
					echo LR\Filters::escapeHtmlAttr($addon->name) /* line 65 */;
					echo '">
';
				} else /* line 66 */ {
					echo '                                                <div class="d-flex justify-content-center align-items-center h-100 bg-light rounded">
                                                    <i class="fas fa-cube fa-2x text-secondary"></i>
                                                </div>
';
				}
				echo '                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <h5 class="mb-1">
                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:detail', [$addon->slug])) /* line 75 */;
				echo '" class="text-decoration-none">';
				echo LR\Filters::escapeHtmlText($addon->name) /* line 75 */;
				echo '</a>
                                        </h5>
                                        <div class="mb-2">
';
				for ($i = 1;
				$i <= 5;
				$i++) /* line 78 */ {
					echo '                                                <i class="fas fa-star ';
					if ($i <= $addon->rating) /* line 79 */ {
						echo 'text-warning';
					} else /* line 79 */ {
						echo 'text-muted';
					}
					echo '"></i>
';

				}
				echo '                                            <small class="text-muted ms-1">(';
				echo LR\Filters::escapeHtmlText($addon->rating) /* line 81 */;
				echo ')</small>
                                            <small class="text-muted ms-3">
                                                <i class="fas fa-download me-1"></i> ';
				echo LR\Filters::escapeHtmlText($addon->downloads_count) /* line 83 */;
				echo ' stažení
                                            </small>
                                        </div>
                                        <p class="mb-1 small">
                                            <strong>Verze:</strong> ';
				echo LR\Filters::escapeHtmlText($addon->version) /* line 87 */;
				echo "\n";
				if ($addon->kodi_version_min || $addon->kodi_version_max) /* line 88 */ {
					echo '                                                <span class="ms-3">
                                                    <strong>Kodi:</strong> 
';
					if ($addon->kodi_version_min && $addon->kodi_version_max) /* line 91 */ {
						echo '                                                        ';
						echo LR\Filters::escapeHtmlText($addon->kodi_version_min) /* line 92 */;
						echo ' - ';
						echo LR\Filters::escapeHtmlText($addon->kodi_version_max) /* line 92 */;
						echo "\n";
					} elseif ($addon->kodi_version_min) /* line 93 */ {
						echo '                                                        ';
						echo LR\Filters::escapeHtmlText($addon->kodi_version_min) /* line 94 */;
						echo '+
';
					} elseif ($addon->kodi_version_max) /* line 95 */ {
						echo '                                                        až ';
						echo LR\Filters::escapeHtmlText($addon->kodi_version_max) /* line 96 */;
						echo "\n";
					}


					echo '                                                </span>
';
				}
				echo '                                        </p>
                                        <p class="mb-0 small text-muted">
';
				if ($addon->description) /* line 102 */ {
					echo '                                                ';
					echo LR\Filters::escapeHtmlText(($this->filters->truncate)($addon->description, 150)) /* line 103 */;
					echo "\n";
				} else /* line 104 */ {
					echo '                                                Žádný popis není k dispozici.
';
				}
				echo '                                        </p>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-center justify-content-end">
                                        <div class="btn-group">
                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:detail', [$addon->slug])) /* line 111 */;
				echo '" class="btn btn-outline-primary">
                                                <i class="fas fa-info-circle me-1"></i> Detail
                                            </a>
                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:download', [$addon->slug])) /* line 114 */;
				echo '" class="btn btn-primary">
                                                <i class="fas fa-download me-1"></i> Stáhnout
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
';

			}

			echo '                    </div>
                    
                    <!-- Pagination -->
';
			if ($results['addons']->getPages() > 1) /* line 125 */ {
				echo '                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item ';
				if (!$results['addons']->hasPreviousPage()) /* line 128 */ {
					echo 'disabled';
				}
				echo '">
                                    <a class="page-link" href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this', ['query' => $query, 'page' => $results['addons']->getPreviousPage()])) /* line 129 */;
				echo '">&laquo; Předchozí</a>
                                </li>
                                
';
				for ($i = 1;
				$i <= $results['addons']->getPages();
				$i++) /* line 132 */ {
					echo '                                    <li class="page-item ';
					if ($i == $page) /* line 133 */ {
						echo 'active';
					}
					echo '">
                                        <a class="page-link" href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this', ['query' => $query, 'page' => $i])) /* line 134 */;
					echo '">';
					echo LR\Filters::escapeHtmlText($i) /* line 134 */;
					echo '</a>
                                    </li>
';

				}
				echo '                                
                                <li class="page-item ';
				if (!$results['addons']->hasNextPage()) /* line 138 */ {
					echo 'disabled';
				}
				echo '">
                                    <a class="page-link" href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this', ['query' => $query, 'page' => $results['addons']->getNextPage()])) /* line 139 */;
				echo '">Další &raquo;</a>
                                </li>
                            </ul>
                        </nav>
';
			}
			echo '                </div>
            </div>
';
		} elseif ($query || isset($filters) && count($filters) > 0) /* line 146 */ {
			echo '            <div class="alert alert-warning">
                <i class="fas fa-info-circle me-1"></i> Nebyly nalezeny žádné doplňky odpovídající vašim kritériím. Zkuste upravit filtry nebo <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:add')) /* line 148 */;
			echo '" class="alert-link">přidejte nový doplněk</a>.
            </div>
';
		} else /* line 150 */ {
			echo '            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h3>Pokročilé vyhledávání doplňků</h3>
                    <p class="text-muted">Použijte filtry na levé straně pro vyhledávání doplňků podle různých kritérií.</p>
                </div>
            </div>
';
		}

		echo '    </div>
</div>
';
	}
}
