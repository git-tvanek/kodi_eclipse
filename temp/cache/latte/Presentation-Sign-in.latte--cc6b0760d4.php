<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\eclipse\app\Presentation\Sign/in.latte */
final class Template_cc6b0760d4 extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\eclipse\\app\\Presentation\\Sign/in.latte';

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
		echo 'Přihlášení';
	}


	/** {block content} on line 3 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Přihlášení</h3>
            </div>
            <div class="card-body">
';
		$ʟ_tmp = $this->global->uiControl->getComponent('signInForm');
		if ($ʟ_tmp instanceof Nette\Application\UI\Renderable) $ʟ_tmp->redrawControl(null, false);
		$ʟ_tmp->render() /* line 11 */;

		echo '                
                <hr class="my-4">
                
                <div class="text-center">
                    <p>Ještě nemáte účet?</p>
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Sign:up')) /* line 17 */;
		echo '" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus me-1"></i> Zaregistrujte se
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
';
	}
}
