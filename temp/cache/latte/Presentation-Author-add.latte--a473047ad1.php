<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\eclipse\app\Presentation\Author/add.latte */
final class Template_a473047ad1 extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\eclipse\\app\\Presentation\\Author/add.latte';

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


	/** {block title} on line 1 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Přidat nového autora';
	}


	/** {block content} on line 3 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="mb-4">
    <h1>Přidat nového autora</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Home:')) /* line 8 */;
		echo '">Domů</a></li>
            <li class="breadcrumb-item"><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Author:')) /* line 9 */;
		echo '">Autoři</a></li>
            <li class="breadcrumb-item active" aria-current="page">Přidat nového autora</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Formulář pro přidání autora</h5>
            </div>
            <div class="card-body">
';
		$ʟ_tmp = $this->global->uiControl->getComponent('authorForm');
		if ($ʟ_tmp instanceof Nette\Application\UI\Renderable) $ʟ_tmp->redrawControl(null, false);
		$ʟ_tmp->render() /* line 22 */;

		echo '            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Nápověda</h5>
            </div>
            <div class="card-body">
                <p>Vyplňte informace o novém autorovi doplňků. Jméno autora je povinné, ostatní údaje jsou volitelné.</p>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-1"></i> <strong>Tip:</strong> Pokud přidáváte autora, který má vlastní webové stránky nebo repozitář, nezapomeňte vyplnit pole "Webová stránka", aby uživatelé mohli snadno najít více informací.
                </div>
                
                <p class="mt-3">Po vytvoření autora budete moci přidat doplňky, které vytvořil.</p>
            </div>
        </div>
    </div>
</div>
';
	}
}
