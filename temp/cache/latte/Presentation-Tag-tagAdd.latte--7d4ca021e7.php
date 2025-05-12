<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\eclipse\app\Presentation\Tag/tagAdd.latte */
final class Template_7d4ca021e7 extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\eclipse\\app\\Presentation\\Tag/tagAdd.latte';

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
		echo 'Přidat nový tag';
	}


	/** {block content} on line 3 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="mb-4">
    <h1>Přidat nový tag</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Home-default')) /* line 8 */;
		echo '">Domů</a></li>
            <li class="breadcrumb-item"><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tag-default')) /* line 9 */;
		echo '">Tagy</a></li>
            <li class="breadcrumb-item active" aria-current="page">Přidat nový tag</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Formulář pro přidání tagu</h5>
            </div>
            <div class="card-body">
';
		$ʟ_tmp = $this->global->uiControl->getComponent('tagForm');
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
                <p>Tagy pomáhají třídit a kategorizovat doplňky. Na rozdíl od kategorií, doplněk může mít více tagů.</p>
                
                <h6 class="mt-3">Tipy pro vytváření tagů:</h6>
                <ul>
                    <li>Používejte stručné a výstižné názvy</li>
                    <li>Pište tagy v jednotném čísle (např. "video" místo "videa")</li>
                    <li>Buďte konzistentní ve formátování</li>
                    <li>Vyhýbejte se příliš obecným tagům</li>
                </ul>
                
                <p class="mt-3">Slug bude automaticky vygenerován z názvu tagu. Můžete ho změnit, pokud potřebujete jiný formát URL.</p>
            </div>
        </div>
    </div>
</div>
';
	}
}
