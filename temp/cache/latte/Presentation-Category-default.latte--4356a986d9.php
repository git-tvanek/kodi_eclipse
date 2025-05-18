<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\kodi_eclipse\app\Presentation\Category/default.latte */
final class Template_4356a986d9 extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\kodi_eclipse\\app\\Presentation\\Category/default.latte';

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
			foreach (array_intersect_key(['category' => '13, 75', 'subcategory' => '34'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block title} on line 1 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Kategorie';
	}


	/** {block content} on line 3 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-folder me-2"></i>Kategorie doplňků</h1>
    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Category:add')) /* line 6 */;
		echo '" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Přidat kategorii
    </a>
</div>

';
		if (isset($categoryHierarchy) && count($categoryHierarchy) > 0) /* line 11 */ {
			echo '    <div class="row row-cols-1 row-cols-md-3 g-4">
';
			foreach ($categoryHierarchy as $category) /* line 13 */ {
				echo '            <div class="col">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Category:detail', [$category['category']->slug])) /* line 18 */;
				echo '" class="text-decoration-none">
                                <i class="fas fa-folder me-2"></i> ';
				echo LR\Filters::escapeHtmlText($category['category']->name) /* line 19 */;
				echo '
                            </a>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>
                            <span class="badge bg-primary rounded-pill">';
				echo LR\Filters::escapeHtmlText($category['addon_count']) /* line 25 */;
				echo ' doplňků</span>
';
				if (count($category['subcategories']) > 0) /* line 26 */ {
					echo '                                <span class="badge bg-secondary rounded-pill">';
					echo LR\Filters::escapeHtmlText(count($category['subcategories'])) /* line 27 */;
					echo ' podkategorií</span>
';
				}
				echo '                        </p>
                        
';
				if (count($category['subcategories']) > 0) /* line 31 */ {
					echo '                            <h6 class="mt-3 mb-2">Podkategorie:</h6>
                            <div class="list-group">
';
					foreach ($category['subcategories'] as $subcategory) /* line 34 */ {
						echo '                                    <a href="';
						echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Category:detail', [$subcategory['category']->slug])) /* line 35 */;
						echo '" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        ';
						echo LR\Filters::escapeHtmlText($subcategory['category']->name) /* line 36 */;
						echo '
                                        <span class="badge bg-primary rounded-pill">';
						echo LR\Filters::escapeHtmlText($subcategory['addon_count']) /* line 37 */;
						echo '</span>
                                    </a>
';

					}

					echo '                            </div>
';
				}
				echo '                    </div>
                    <div class="card-footer">
                        <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Category:detail', [$category['category']->slug])) /* line 44 */;
				echo '" class="btn btn-sm btn-primary w-100">
                            Procházet doplňky
                        </a>
                    </div>
                </div>
            </div>
';

			}

			echo '    </div>
';
		} else /* line 52 */ {
			echo '    <div class="alert alert-info">
        <i class="fas fa-info-circle me-1"></i> Žádné kategorie nebyly nalezeny. <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Category:add')) /* line 54 */;
			echo '" class="alert-link">Vytvořte první kategorii</a>.
    </div>
';
		}
		echo "\n";
		if (isset($popularCategories) && count($popularCategories) > 0) /* line 58 */ {
			echo '    <div class="card mt-5">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-chart-pie me-1"></i> Nejpopulárnější kategorie</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kategorie</th>
                            <th>Počet doplňků</th>
                            <th>Celkem stažení</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
';
			foreach ($popularCategories as $category) /* line 75 */ {
				echo '                            <tr>
                                <td>
                                    <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Category:detail', [$category['category']->slug])) /* line 78 */;
				echo '" class="text-decoration-none">
                                        ';
				echo LR\Filters::escapeHtmlText($category['category']->name) /* line 79 */;
				echo '
                                    </a>
                                </td>
                                <td>';
				echo LR\Filters::escapeHtmlText($category['addon_count']) /* line 82 */;
				echo '</td>
                                <td>';
				echo LR\Filters::escapeHtmlText($category['total_downloads']) /* line 83 */;
				echo '</td>
                                <td>
                                    <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Category:detail', [$category['category']->slug])) /* line 85 */;
				echo '" class="btn btn-sm btn-outline-primary">
                                        Procházet
                                    </a>
                                </td>
                            </tr>
';

			}

			echo '                    </tbody>
                </table>
            </div>
        </div>
    </div>
';
		}
	}
}
