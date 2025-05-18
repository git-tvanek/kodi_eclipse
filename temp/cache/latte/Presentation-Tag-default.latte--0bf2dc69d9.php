<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\kodi_eclipse\app\Presentation\Tag/default.latte */
final class Template_0bf2dc69d9 extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\kodi_eclipse\\app\\Presentation\\Tag/default.latte';

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
			foreach (array_intersect_key(['tag' => '30, 92, 111'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block title} on line 1 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Tagy';
	}


	/** {block content} on line 3 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-tags me-2"></i>Tagy doplňků</h1>
    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tag-add')) /* line 6 */;
		echo '" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Přidat tag
    </a>
</div>

';
		if (isset($tagsWithCounts) && count($tagsWithCounts) > 0) /* line 11 */ {
			echo '    <div class="row">
        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Seznam tagů</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Název</th>
                                    <th>Slug</th>
                                    <th>Počet doplňků</th>
                                    <th>Akce</th>
                                </tr>
                            </thead>
                            <tbody>
';
			foreach ($tagsWithCounts as $tag) /* line 30 */ {
				echo '                                    <tr>
                                        <td>
                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('detail', [$tag['slug']])) /* line 33 */;
				echo '" class="text-decoration-none fw-bold">
                                                ';
				echo LR\Filters::escapeHtmlText($tag['name']) /* line 34 */;
				echo '
                                            </a>
                                        </td>
                                        <td><code>';
				echo LR\Filters::escapeHtmlText($tag['slug']) /* line 37 */;
				echo '</code></td>
                                        <td>';
				echo LR\Filters::escapeHtmlText($tag['addon_count']) /* line 38 */;
				echo '</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('detail', [$tag['slug']])) /* line 41 */;
				echo '" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$tag['id']])) /* line 44 */;
				echo '" class="btn btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$tag['id']])) /* line 47 */;
				echo '" onclick="return confirm(\'Opravdu chcete smazat tento tag?\');" class="btn btn-outline-danger">
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
			if (isset($page) && isset($pages) && $pages > 1) /* line 60 */ {
				echo '                    <div class="card-footer">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item ';
				if ($page <= 1) /* line 64 */ {
					echo 'disabled';
				}
				echo '">
                                    <a class="page-link" href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tag-default', ['page' => $page - 1])) /* line 65 */;
				echo '">&laquo; Předchozí</a>
                                </li>
                                
';
				for ($i = 1;
				$i <= $pages;
				$i++) /* line 68 */ {
					echo '                                    <li class="page-item ';
					if ($i == $page) /* line 69 */ {
						echo 'active';
					}
					echo '">
                                        <a class="page-link" href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tag-default', ['page' => $i])) /* line 70 */;
					echo '">';
					echo LR\Filters::escapeHtmlText($i) /* line 70 */;
					echo '</a>
                                    </li>
';

				}
				echo '                                
                                <li class="page-item ';
				if ($page >= $pages) /* line 74 */ {
					echo 'disabled';
				}
				echo '">
                                    <a class="page-link" href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tag-default', ['page' => $page + 1])) /* line 75 */;
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
			if (isset($tagCloud) && count($tagCloud) > 0) /* line 85 */ {
				echo '                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-cloud me-1"></i> Tag Cloud</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
';
				foreach ($tagCloud as $tag) /* line 92 */ {
					echo '                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('detail', [$tag['slug']])) /* line 93 */;
					echo '" 
                                   class="btn btn-sm btn-outline-secondary" 
                                   style="font-size: ';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeCss($tag['normalized_weight'] * 0.5 + 0.7)) /* line 95 */;
					echo 'rem">
                                    ';
					echo LR\Filters::escapeHtmlText($tag['name']) /* line 96 */;
					echo '
                                </a>
';

				}

				echo '                        </div>
                    </div>
                </div>
';
			}
			echo '            
';
			if (isset($trendingTags) && count($trendingTags) > 0) /* line 104 */ {
				echo '                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-fire me-1"></i> Trendující tagy</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
';
				foreach ($trendingTags as $tag) /* line 111 */ {
					echo '                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('detail', [$tag['tag']->slug])) /* line 112 */;
					echo '" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    ';
					echo LR\Filters::escapeHtmlText($tag['tag']->name) /* line 113 */;
					echo '
                                    <span class="badge bg-primary rounded-pill">';
					echo LR\Filters::escapeHtmlText($tag['usage_count']) /* line 114 */;
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
		} else /* line 123 */ {
			echo '    <div class="alert alert-info">
        <i class="fas fa-info-circle me-1"></i> Žádné tagy nebyly nalezeny. <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 125 */;
			echo '" class="alert-link">Přidejte první tag</a>.
    </div>
';
		}
	}
}
