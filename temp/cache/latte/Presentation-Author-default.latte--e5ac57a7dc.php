<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\eclipse\app\Presentation\Author/default.latte */
final class Template_e5ac57a7dc extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\eclipse\\app\\Presentation\\Author/default.latte';

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
			foreach (array_intersect_key(['author' => '30', 'topAuthor' => '101'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block title} on line 1 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Autoři';
	}


	/** {block content} on line 3 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-users me-2"></i>Autoři doplňků</h1>
    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Author:add')) /* line 6 */;
		echo '" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Přidat autora
    </a>
</div>

';
		if (isset($authors) && $authors->getItems()->count() > 0) /* line 11 */ {
			echo '    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Seznam autorů</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Název</th>
                                    <th>Kontakt</th>
                                    <th>Přidáno</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
';
			foreach ($authors->getItems() as $author) /* line 30 */ {
				echo '                                    <tr>
                                        <td>
                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Author:detail', [$author->id])) /* line 33 */;
				echo '" class="text-decoration-none fw-bold">
                                                ';
				echo LR\Filters::escapeHtmlText($author->name) /* line 34 */;
				echo '
                                            </a>
                                        </td>
                                        <td>
';
				if ($author->email) /* line 38 */ {
					echo '                                                <a href="mailto:';
					echo LR\Filters::escapeHtmlAttr($author->email) /* line 39 */;
					echo '">';
					echo LR\Filters::escapeHtmlText($author->email) /* line 39 */;
					echo '</a><br>
';
				}
				if ($author->website) /* line 41 */ {
					echo '                                                <a href="';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($author->website)) /* line 42 */;
					echo '" target="_blank" class="small text-truncate d-inline-block" style="max-width: 200px;">
                                                    ';
					echo LR\Filters::escapeHtmlText($author->website) /* line 43 */;
					echo '
                                                </a>
';
				}
				echo '                                        </td>
                                        <td>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($author->created_at, 'j.n.Y')) /* line 47 */;
				echo '</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Author:detail', [$author->id])) /* line 50 */;
				echo '" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Author:edit', [$author->id])) /* line 53 */;
				echo '" class="btn btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Author:delete', [$author->id])) /* line 56 */;
				echo '" onclick="return confirm(\'Opravdu chcete smazat tohoto autora?\');" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
';

			}

			echo '                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
';
			if ($authors->getPages() > 1) /* line 69 */ {
				echo '                    <div class="card-footer">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item ';
				if (!$authors->hasPreviousPage()) /* line 73 */ {
					echo 'disabled';
				}
				echo '">
                                    <a class="page-link" href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this', ['page' => $authors->getPreviousPage()])) /* line 74 */;
				echo '">&laquo; Předchozí</a>
                                </li>
                                
';
				for ($i = 1;
				$i <= $authors->getPages();
				$i++) /* line 77 */ {
					echo '                                    <li class="page-item ';
					if ($i == $page) /* line 78 */ {
						echo 'active';
					}
					echo '">
                                        <a class="page-link" href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this', ['page' => $i])) /* line 79 */;
					echo '">';
					echo LR\Filters::escapeHtmlText($i) /* line 79 */;
					echo '</a>
                                    </li>
';

				}
				echo '                                
                                <li class="page-item ';
				if (!$authors->hasNextPage()) /* line 83 */ {
					echo 'disabled';
				}
				echo '">
                                    <a class="page-link" href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('this', ['page' => $authors->getNextPage()])) /* line 84 */;
				echo '">Další &raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
';
			}
			echo '            </div>
        </div>
        
        <div class="col-md-3">
';
			if (isset($topAuthors) && count($topAuthors) > 0) /* line 94 */ {
				echo '                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-crown me-1"></i> Top autoři</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
';
				foreach ($topAuthors as $topAuthor) /* line 101 */ {
					echo '                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Author:detail', [$topAuthor['author']->id])) /* line 102 */;
					echo '" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold">';
					echo LR\Filters::escapeHtmlText($topAuthor['author']->name) /* line 104 */;
					echo '</div>
                                        <small class="text-muted">';
					echo LR\Filters::escapeHtmlText($topAuthor['addon_count']) /* line 105 */;
					echo ' doplňků</small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">';
					echo LR\Filters::escapeHtmlText($topAuthor['total_downloads']) /* line 107 */;
					echo '</span>
                                </a>
';

				}

				echo '                        </div>
                    </div>
                </div>
';
			}
			echo '        </div>
    </div>
';
		} else /* line 116 */ {
			echo '    <div class="alert alert-info">
        <i class="fas fa-info-circle me-1"></i> Žádní autoři nebyli nalezeni. <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Author:add')) /* line 118 */;
			echo '" class="alert-link">Přidejte prvního autora</a>.
    </div>
';
		}
	}
}
