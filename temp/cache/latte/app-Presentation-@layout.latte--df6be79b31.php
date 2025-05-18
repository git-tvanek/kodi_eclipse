<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: C:\xampp\htdocs\kodi_eclipse\app\Presentation/@layout.latte */
final class Template_df6be79b31 extends Latte\Runtime\Template
{
	public const Source = 'C:\\xampp\\htdocs\\kodi_eclipse\\app\\Presentation/@layout.latte';

	public const Blocks = [
		['head' => 'blockHead', 'scripts' => 'blockScripts'],
	];


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		echo '<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>';
		if ($this->hasBlock('title')) /* line 6 */ {
			$this->renderBlock('title', [], function ($s, $type) {
				$ʟ_fi = new LR\FilterInfo($type);
				return LR\Filters::convertTo($ʟ_fi, 'html', $this->filters->filterContent('stripHtml', $ʟ_fi, $s));
			}) /* line 6 */;
			echo ' | ';
		}
		echo 'xAddons</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 15 */;
		echo '/css/style.css">
    
';
		$this->renderBlock('head', get_defined_vars()) /* line 17 */;
		echo '</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Home:')) /* line 23 */;
		echo '">
                <i class="fas fa-puzzle-piece me-2"></i>xAddons
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link ';
		if ($presenter->getName() === 'Addon') /* line 33 */ {
			echo 'active';
		}
		echo '" href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:')) /* line 33 */;
		echo '">
                            <i class="fas fa-cube me-1"></i> Doplňky
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ';
		if ($presenter->getName() === 'Category') /* line 38 */ {
			echo 'active';
		}
		echo '" href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Category:')) /* line 38 */;
		echo '">
                            <i class="fas fa-folder me-1"></i> Kategorie
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ';
		if ($presenter->getName() === 'Author') /* line 43 */ {
			echo 'active';
		}
		echo '" href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Author:')) /* line 43 */;
		echo '">
                            <i class="fas fa-users me-1"></i> Autoři
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ';
		if ($presenter->getName() === 'Tag') /* line 48 */ {
			echo 'active';
		}
		echo '" href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tag:')) /* line 48 */;
		echo '">
                            <i class="fas fa-tags me-1"></i> Tagy
                        </a>
                    </li>
                    
';
		if (isset($userLoggedIn) && $userLoggedIn && isset($isAdmin) && $isAdmin) /* line 54 */ {
			echo '                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cogs me-1"></i> Administrace
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                <li><a class="dropdown-item" href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Dashboard:')) /* line 60 */;
			echo '"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Dashboard:addonStats')) /* line 62 */;
			echo '"><i class="fas fa-chart-line me-1"></i> Statistiky doplňků</a></li>
                                <li><a class="dropdown-item" href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Dashboard:categoryStats')) /* line 63 */;
			echo '"><i class="fas fa-chart-pie me-1"></i> Statistiky kategorií</a></li>
                                <li><a class="dropdown-item" href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Dashboard:authorStats')) /* line 64 */;
			echo '"><i class="fas fa-user-chart me-1"></i> Statistiky autorů</a></li>
                                <li><a class="dropdown-item" href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Dashboard:reviewStats')) /* line 65 */;
			echo '"><i class="fas fa-star me-1"></i> Statistiky recenzí</a></li>
                            </ul>
                        </li>
';
		}
		echo '                </ul>
                
                <form class="d-flex me-3" action="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Search:')) /* line 71 */;
		echo '" method="get">
                    <input class="form-control me-2" type="search" name="query" placeholder="Hledat doplňky..." 
                           value="';
		echo LR\Filters::escapeHtmlAttr($presenter->getParameter('query')) /* line 73 */;
		echo '">
                    <button class="btn btn-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
';
		if (isset($userLoggedIn) && $userLoggedIn) /* line 80 */ {
			echo '                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> ';
			echo LR\Filters::escapeHtmlText($user->identity->username ?? 'Uživatel') /* line 83 */;
			echo '
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('User:profile')) /* line 86 */;
			echo '"><i class="fas fa-user me-1"></i> Můj profil</a></li>
                            <li><a class="dropdown-item" href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('User:myAddons')) /* line 87 */;
			echo '"><i class="fas fa-cube me-1"></i> Moje doplňky</a></li>
                            <li><a class="dropdown-item" href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('User:myReviews')) /* line 88 */;
			echo '"><i class="fas fa-star me-1"></i> Moje recenze</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Sign:out')) /* line 90 */;
			echo '"><i class="fas fa-sign-out-alt me-1"></i> Odhlásit se</a></li>
                        </ul>
                    </div>
';
		} else /* line 93 */ {
			echo '                    <div class="d-flex">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Sign:in')) /* line 95 */;
			echo '" class="btn btn-outline-light me-2">
                            <i class="fas fa-sign-in-alt me-1"></i> Přihlásit
                        </a>
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Sign:up')) /* line 98 */;
			echo '" class="btn btn-light">
                            <i class="fas fa-user-plus me-1"></i> Registrovat
                        </a>
                    </div>
';
		}
		echo '            </div>
        </div>
    </nav>
    
    <!-- Flash messages -->
    <div class="container mt-3">
';
		foreach ($flashes as $flash) /* line 109 */ {
			echo '        <div class="alert alert-';
			echo LR\Filters::escapeHtmlAttr($flash->type) /* line 109 */;
			echo ' alert-dismissible fade show">
            ';
			echo LR\Filters::escapeHtmlText($flash->message) /* line 110 */;
			echo '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
';

		}

		echo '    </div>
    
    <!-- Main content -->
    <main class="container py-4">
';
		$this->renderBlock('content', [], 'html') /* line 117 */;
		echo '    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>xAddons</h5>
                    <p>Objevujte a sdílejte nejlepší doplňky pro Kodi media center.</p>
                </div>
                <div class="col-md-3">
                    <h5>Rychlé odkazy</h5>
                    <ul class="list-unstyled">
                        <li><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Home:')) /* line 131 */;
		echo '" class="text-white">Domů</a></li>
                        <li><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Addon:')) /* line 132 */;
		echo '" class="text-white">Doplňky</a></li>
                        <li><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Category:')) /* line 133 */;
		echo '" class="text-white">Kategorie</a></li>
                        <li><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Search:advanced')) /* line 134 */;
		echo '" class="text-white">Pokročilé vyhledávání</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Zdroje</h5>
                    <ul class="list-unstyled">
                        <li><a href="https://kodi.tv/" class="text-white" target="_blank">Oficiální stránka Kodi</a></li>
                        <li><a href="https://kodi.wiki/" class="text-white" target="_blank">Kodi Wiki</a></li>
                        <li><a href="#" class="text-white">Nápověda & Dokumentace</a></li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; ';
		echo LR\Filters::escapeHtmlText(date('Y')) /* line 148 */;
		echo ' xAddons.cz. Všechna práva vyhrazena.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
';
		$this->renderBlock('scripts', get_defined_vars()) /* line 156 */;
		echo '</body>
</html>';
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['flash' => '109'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block head} on line 17 */
	public function blockHead(array $ʟ_args): void
	{
	}


	/** {block scripts} on line 156 */
	public function blockScripts(array $ʟ_args): void
	{
	}
}
