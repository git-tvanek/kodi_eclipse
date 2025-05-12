<?php
// source: C:\xampp\htdocs\eclipse/config/common.neon
// source: C:\xampp\htdocs\eclipse/config/services.neon
// source: array

/** @noinspection PhpParamsInspection,PhpMethodMayBeStaticInspection */

declare(strict_types=1);

class Container_5520ba1493 extends Nette\DI\Container
{
	protected array $aliases = [
		'application' => 'application.application',
		'cacheStorage' => 'cache.storage',
		'database.default' => 'database.default.connection',
		'database.default.context' => 'database.default.explorer',
		'httpRequest' => 'http.request',
		'httpResponse' => 'http.response',
		'nette.cacheJournal' => 'cache.journal',
		'nette.database.default' => 'database.default',
		'nette.database.default.context' => 'database.default.explorer',
		'nette.httpRequestFactory' => 'http.requestFactory',
		'nette.latteFactory' => 'latte.latteFactory',
		'nette.mailer' => 'mail.mailer',
		'nette.presenterFactory' => 'application.presenterFactory',
		'nette.templateFactory' => 'latte.templateFactory',
		'nette.userStorage' => 'security.userStorage',
		'session' => 'session.session',
		'user' => 'security.user',
	];

	protected array $wiring = [
		'Nette\DI\Container' => [['container']],
		'Nette\Application\Application' => [['application.application']],
		'Nette\Application\IPresenterFactory' => [['application.presenterFactory']],
		'Nette\Application\LinkGenerator' => [['application.linkGenerator']],
		'Nette\Caching\Storages\Journal' => [['cache.journal']],
		'Nette\Caching\Storage' => [['cache.storage']],
		'Nette\Database\Connection' => [['database.default.connection']],
		'Nette\Database\IStructure' => [['database.default.structure']],
		'Nette\Database\Structure' => [['database.default.structure']],
		'Nette\Database\Conventions' => [['database.default.conventions']],
		'Nette\Database\Conventions\DiscoveredConventions' => [['database.default.conventions']],
		'Nette\Database\Explorer' => [['database.default.explorer']],
		'Nette\Http\RequestFactory' => [['http.requestFactory']],
		'Nette\Http\IRequest' => [['http.request']],
		'Nette\Http\Request' => [['http.request']],
		'Nette\Http\IResponse' => [['http.response']],
		'Nette\Http\Response' => [['http.response']],
		'Nette\Bridges\ApplicationLatte\LatteFactory' => [['latte.latteFactory']],
		'Nette\Application\UI\TemplateFactory' => [['latte.templateFactory']],
		'Nette\Bridges\ApplicationLatte\TemplateFactory' => [['latte.templateFactory']],
		'Nette\Mail\Mailer' => [['mail.mailer']],
		'Nette\Security\Passwords' => [['security.passwords']],
		'Nette\Security\UserStorage' => [['security.userStorage']],
		'Nette\Security\User' => [['security.user']],
		'Nette\Http\Session' => [['session.session']],
		'Tracy\ILogger' => [['tracy.logger']],
		'Tracy\BlueScreen' => [['tracy.blueScreen']],
		'Tracy\Bar' => [['tracy.bar']],
		'Nette\Routing\RouteList' => [['01']],
		'Nette\Routing\Router' => [['01']],
		'ArrayAccess' => [
			2 => [
				'01',
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
			],
		],
		'Nette\Application\Routers\RouteList' => [['01']],
		'App\Repository\BaseRepository' => [
			[
				'addonRepository',
				'authorRepository',
				'categoryRepository',
				'reviewRepository',
				'tagRepository',
				'027',
				'028',
				'029',
			],
		],
		'App\Repository\Interface\IBaseRepository' => [
			0 => [
				'addonRepository',
				'authorRepository',
				'categoryRepository',
				'reviewRepository',
				'tagRepository',
				'027',
				'028',
				'029',
			],
			2 => [
				5 => 'addonRepositoryAlias',
				'authorRepositoryAlias',
				'categoryRepositoryAlias',
				'reviewRepositoryAlias',
				'tagRepositoryAlias',
			],
		],
		'App\Repository\Interface\IAddonRepository' => [0 => ['addonRepository'], 2 => [1 => 'addonRepositoryAlias']],
		'App\Repository\AddonRepository' => [['addonRepository']],
		'App\Repository\Interface\IAuthorRepository' => [0 => ['authorRepository'], 2 => [1 => 'authorRepositoryAlias']],
		'App\Repository\AuthorRepository' => [['authorRepository']],
		'App\Repository\Interface\ICategoryRepository' => [
			0 => ['categoryRepository'],
			2 => [1 => 'categoryRepositoryAlias'],
		],
		'App\Repository\CategoryRepository' => [['categoryRepository']],
		'App\Repository\Interface\IReviewRepository' => [0 => ['reviewRepository'], 2 => [1 => 'reviewRepositoryAlias']],
		'App\Repository\ReviewRepository' => [['reviewRepository']],
		'App\Repository\Interface\ITagRepository' => [0 => ['tagRepository'], 2 => [1 => 'tagRepositoryAlias']],
		'App\Repository\TagRepository' => [['tagRepository']],
		'App\Factory\Interface\IAddonFactory' => [0 => ['addonFactory'], 2 => [1 => 'addonFactoryAlias']],
		'App\Factory\Interface\IFactory' => [
			0 => [
				'addonFactory',
				'addonTagFactory',
				'authorFactory',
				'categoryFactory',
				'reviewFactory',
				'screenshotFactory',
				'tagFactory',
			],
			2 => [
				7 => 'addonFactoryAlias',
				'addonTagFactoryAlias',
				'authorFactoryAlias',
				'categoryFactoryAlias',
				'reviewFactoryAlias',
				'screenshotFactoryAlias',
				'tagFactoryAlias',
			],
		],
		'App\Factory\AddonFactory' => [['addonFactory']],
		'App\Factory\Interface\IAddonTagFactory' => [0 => ['addonTagFactory'], 2 => [1 => 'addonTagFactoryAlias']],
		'App\Factory\AddonTagFactory' => [['addonTagFactory']],
		'App\Factory\Interface\IAuthorFactory' => [0 => ['authorFactory'], 2 => [1 => 'authorFactoryAlias']],
		'App\Factory\AuthorFactory' => [['authorFactory']],
		'App\Factory\Interface\ICategoryFactory' => [0 => ['categoryFactory'], 2 => [1 => 'categoryFactoryAlias']],
		'App\Factory\CategoryFactory' => [['categoryFactory']],
		'App\Factory\Interface\IReviewFactory' => [0 => ['reviewFactory'], 2 => [1 => 'reviewFactoryAlias']],
		'App\Factory\ReviewFactory' => [['reviewFactory']],
		'App\Factory\Interface\IScreenshotFactory' => [0 => ['screenshotFactory'], 2 => [1 => 'screenshotFactoryAlias']],
		'App\Factory\ScreenshotFactory' => [['screenshotFactory']],
		'App\Factory\Interface\ITagFactory' => [0 => ['tagFactory'], 2 => [1 => 'tagFactoryAlias']],
		'App\Factory\TagFactory' => [['tagFactory']],
		'App\Forms\Factory\AddonFormFactory' => [['02']],
		'App\Forms\Factory\AuthorFormFactory' => [['03']],
		'App\Forms\Factory\CategoryFormFactory' => [['04']],
		'App\Forms\Factory\ReviewFormFactory' => [['05']],
		'App\Forms\Factory\SearchFormFactory' => [['06']],
		'App\Forms\Factory\TagFormFactory' => [['07']],
		'App\Service\BaseService' => [
			['addonService', 'authorService', 'categoryService', 'reviewService', 'tagService', '031', '032', '033'],
		],
		'App\Service\IBaseService' => [
			0 => ['addonService', 'authorService', 'categoryService', 'reviewService', 'tagService', '031', '032', '033'],
			2 => [
				5 => 'addonServiceAlias',
				'authorServiceAlias',
				'categoryServiceAlias',
				'reviewServiceAlias',
				'tagServiceAlias',
			],
		],
		'App\Service\IAddonService' => [0 => ['addonService'], 2 => [1 => 'addonServiceAlias']],
		'App\Service\AddonService' => [['addonService']],
		'App\Service\IAuthorService' => [0 => ['authorService'], 2 => [1 => 'authorServiceAlias']],
		'App\Service\AuthorService' => [['authorService']],
		'App\Service\ICategoryService' => [0 => ['categoryService'], 2 => [1 => 'categoryServiceAlias']],
		'App\Service\CategoryService' => [['categoryService']],
		'App\Service\IReviewService' => [0 => ['reviewService'], 2 => [1 => 'reviewServiceAlias']],
		'App\Service\ReviewService' => [['reviewService']],
		'App\Service\ISearchService' => [0 => ['searchService'], 2 => [1 => 'searchServiceAlias']],
		'App\Service\SearchService' => [['searchService']],
		'App\Service\IStatisticsService' => [0 => ['statisticsService'], 2 => [1 => 'statisticsServiceAlias']],
		'App\Service\StatisticsService' => [['statisticsService']],
		'App\Service\ITagService' => [0 => ['tagService'], 2 => [1 => 'tagServiceAlias']],
		'App\Service\TagService' => [['tagService']],
		'App\Facade\IFacade' => [['08', '09', '010', '011', '012', '013', '014', '016']],
		'App\Facade\AddonFacade' => [['08']],
		'App\Facade\AuthorFacade' => [['09']],
		'App\Facade\CategoryFacade' => [['010']],
		'App\Facade\ReviewFacade' => [['011']],
		'App\Facade\SearchFacade' => [['012']],
		'App\Facade\StatisticsFacade' => [['013']],
		'App\Facade\TagFacade' => [['014']],
		'App\Presentation\BasePresenter' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
			],
		],
		'Nette\Application\UI\Presenter' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
			],
		],
		'Nette\Application\UI\Control' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
			],
		],
		'Nette\Application\UI\Component' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
			],
		],
		'Nette\ComponentModel\Container' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
			],
		],
		'Nette\ComponentModel\Component' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
			],
		],
		'Nette\ComponentModel\IComponent' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
			],
		],
		'Nette\ComponentModel\IContainer' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
			],
		],
		'Nette\Application\UI\SignalReceiver' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
			],
		],
		'Nette\Application\UI\StatePersistent' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
			],
		],
		'Nette\Application\UI\Renderable' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
			],
		],
		'Nette\Application\IPresenter' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
				'application.13',
				'application.14',
			],
		],
		'App\Presentation\Addon\AddonPresenter' => [2 => ['application.1']],
		'App\Presentation\Author\AuthorPresenter' => [2 => ['application.2']],
		'App\Presentation\Category\CategoryPresenter' => [2 => ['application.3']],
		'App\Presentation\Dashboard\DashboardPresenter' => [2 => ['application.4']],
		'App\Presentation\Error\Error4xx\Error4xxPresenter' => [2 => ['application.5']],
		'App\Presentation\Error\Error5xx\Error5xxPresenter' => [2 => ['application.6']],
		'App\Presentation\Home\HomePresenter' => [2 => ['application.7']],
		'App\Presentation\Review\ReviewPresenter' => [2 => ['application.8']],
		'App\Presentation\Search\SearchPresenter' => [2 => ['application.9']],
		'App\Presentation\Sign\SignPresenter' => [2 => ['application.10']],
		'App\Presentation\Tag\TagPresenter' => [2 => ['application.11']],
		'App\Presentation\User\UserPresenter' => [2 => ['application.12']],
		'NetteModule\ErrorPresenter' => [2 => ['application.13']],
		'NetteModule\MicroPresenter' => [2 => ['application.14']],
		'App\Dto\Factory\DtoFactory' => [['015']],
		'App\Facade\AuthorizationFacade' => [['016']],
		'App\Factory\Interface\IPermissionFactory' => [['017']],
		'App\Factory\PermissionFactory' => [['017']],
		'App\Factory\Interface\IRoleFactory' => [['018']],
		'App\Factory\RoleFactory' => [['018']],
		'App\Factory\Interface\IRolePermissionFactory' => [['019']],
		'App\Factory\RolePermissionFactory' => [['019']],
		'App\Factory\Interface\IUserFactory' => [['020']],
		'App\Factory\UserFactory' => [['020']],
		'App\Factory\Interface\IUserRoleFactory' => [['021']],
		'App\Factory\UserRoleFactory' => [['021']],
		'App\Forms\Factory\PermissionFormFactory' => [['022']],
		'App\Forms\Factory\RoleFormFactory' => [['023']],
		'App\Forms\Factory\RolePermissionFormFactory' => [['024']],
		'App\Forms\Factory\UserFormFactory' => [['025']],
		'App\Forms\Factory\UserRoleFormFactory' => [['026']],
		'App\Repository\Interface\IPermissionRepository' => [['027']],
		'App\Repository\PermissionRepository' => [['027']],
		'App\Repository\Interface\IRoleRepository' => [['028']],
		'App\Repository\RoleRepository' => [['028']],
		'App\Repository\Interface\IUserRepository' => [['029']],
		'App\Repository\UserRepository' => [['029']],
		'App\Service\Interface\IAuthorizationService' => [['030']],
		'App\Service\AuthorizationService' => [['030']],
		'App\Service\IPermissionService' => [['031']],
		'App\Service\PermissionService' => [['031']],
		'App\Service\IRoleService' => [['032']],
		'App\Service\RoleService' => [['032']],
		'App\Service\IUserService' => [['033']],
		'App\Service\UserService' => [['033']],
	];


	public function __construct(array $params = [])
	{
		parent::__construct($params);
	}


	public function createService01(): Nette\Application\Routers\RouteList
	{
		return App\Core\RouterFactory::createRouter();
	}


	public function createService02(): App\Forms\Factory\AddonFormFactory
	{
		return new App\Forms\Factory\AddonFormFactory(
			$this->getService('categoryService'),
			$this->getService('authorService'),
			$this->getService('tagService'),
		);
	}


	public function createService03(): App\Forms\Factory\AuthorFormFactory
	{
		return new App\Forms\Factory\AuthorFormFactory;
	}


	public function createService04(): App\Forms\Factory\CategoryFormFactory
	{
		return new App\Forms\Factory\CategoryFormFactory($this->getService('categoryService'));
	}


	public function createService05(): App\Forms\Factory\ReviewFormFactory
	{
		return new App\Forms\Factory\ReviewFormFactory;
	}


	public function createService06(): App\Forms\Factory\SearchFormFactory
	{
		return new App\Forms\Factory\SearchFormFactory($this->getService('categoryService'), $this->getService('tagService'));
	}


	public function createService07(): App\Forms\Factory\TagFormFactory
	{
		return new App\Forms\Factory\TagFormFactory;
	}


	public function createService08(): App\Facade\AddonFacade
	{
		return new App\Facade\AddonFacade(
			$this->getService('addonService'),
			$this->getService('categoryService'),
			$this->getService('tagService'),
			$this->getService('authorService'),
		);
	}


	public function createService09(): App\Facade\AuthorFacade
	{
		return new App\Facade\AuthorFacade($this->getService('authorService'));
	}


	public function createService010(): App\Facade\CategoryFacade
	{
		return new App\Facade\CategoryFacade($this->getService('categoryService'));
	}


	public function createService011(): App\Facade\ReviewFacade
	{
		return new App\Facade\ReviewFacade($this->getService('reviewService'));
	}


	public function createService012(): App\Facade\SearchFacade
	{
		return new App\Facade\SearchFacade($this->getService('searchService'));
	}


	public function createService013(): App\Facade\StatisticsFacade
	{
		return new App\Facade\StatisticsFacade($this->getService('statisticsService'));
	}


	public function createService014(): App\Facade\TagFacade
	{
		return new App\Facade\TagFacade($this->getService('tagService'));
	}


	public function createService015(): App\Dto\Factory\DtoFactory
	{
		return new App\Dto\Factory\DtoFactory;
	}


	public function createService016(): App\Facade\AuthorizationFacade
	{
		return new App\Facade\AuthorizationFacade($this->getService('030'));
	}


	public function createService017(): App\Factory\PermissionFactory
	{
		return new App\Factory\PermissionFactory;
	}


	public function createService018(): App\Factory\RoleFactory
	{
		return new App\Factory\RoleFactory;
	}


	public function createService019(): App\Factory\RolePermissionFactory
	{
		return new App\Factory\RolePermissionFactory;
	}


	public function createService020(): App\Factory\UserFactory
	{
		return new App\Factory\UserFactory;
	}


	public function createService021(): App\Factory\UserRoleFactory
	{
		return new App\Factory\UserRoleFactory;
	}


	public function createService022(): App\Forms\Factory\PermissionFormFactory
	{
		return new App\Forms\Factory\PermissionFormFactory;
	}


	public function createService023(): App\Forms\Factory\RoleFormFactory
	{
		return new App\Forms\Factory\RoleFormFactory;
	}


	public function createService024(): App\Forms\Factory\RolePermissionFormFactory
	{
		return new App\Forms\Factory\RolePermissionFormFactory($this->getService('031'));
	}


	public function createService025(): App\Forms\Factory\UserFormFactory
	{
		return new App\Forms\Factory\UserFormFactory;
	}


	public function createService026(): App\Forms\Factory\UserRoleFormFactory
	{
		return new App\Forms\Factory\UserRoleFormFactory($this->getService('032'));
	}


	public function createService027(): App\Repository\PermissionRepository
	{
		return new App\Repository\PermissionRepository($this->getService('database.default.explorer'));
	}


	public function createService028(): App\Repository\RoleRepository
	{
		return new App\Repository\RoleRepository($this->getService('database.default.explorer'));
	}


	public function createService029(): App\Repository\UserRepository
	{
		return new App\Repository\UserRepository($this->getService('database.default.explorer'));
	}


	public function createService030(): App\Service\AuthorizationService
	{
		return new App\Service\AuthorizationService(
			$this->getService('029'),
			$this->getService('028'),
			$this->getService('027'),
			$this->getService('cache.storage'),
		);
	}


	public function createService031(): App\Service\PermissionService
	{
		return new App\Service\PermissionService($this->getService('027'), $this->getService('017'));
	}


	public function createService032(): App\Service\RoleService
	{
		return new App\Service\RoleService($this->getService('028'), $this->getService('018'));
	}


	public function createService033(): App\Service\UserService
	{
		return new App\Service\UserService($this->getService('029'), $this->getService('020'));
	}


	public function createServiceAddonFactory(): App\Factory\AddonFactory
	{
		return new App\Factory\AddonFactory;
	}


	public function createServiceAddonFactoryAlias(): App\Factory\Interface\IAddonFactory
	{
		return $this->getService('addonFactory');
	}


	public function createServiceAddonRepository(): App\Repository\AddonRepository
	{
		return new App\Repository\AddonRepository($this->getService('database.default.explorer'));
	}


	public function createServiceAddonRepositoryAlias(): App\Repository\Interface\IAddonRepository
	{
		return $this->getService('addonRepository');
	}


	public function createServiceAddonService(): App\Service\AddonService
	{
		return new App\Service\AddonService(
			$this->getService('addonRepository'),
			$this->getService('addonFactory'),
			$this->getService('screenshotFactory'),
			'C:\xampp\htdocs\eclipse\app/../www/uploads',
		);
	}


	public function createServiceAddonServiceAlias(): App\Service\IAddonService
	{
		return $this->getService('addonService');
	}


	public function createServiceAddonTagFactory(): App\Factory\AddonTagFactory
	{
		return new App\Factory\AddonTagFactory;
	}


	public function createServiceAddonTagFactoryAlias(): App\Factory\Interface\IAddonTagFactory
	{
		return $this->getService('addonTagFactory');
	}


	public function createServiceApplication__1(): App\Presentation\Addon\AddonPresenter
	{
		$service = new App\Presentation\Addon\AddonPresenter(
			$this->getService('08'),
			$this->getService('010'),
			$this->getService('09'),
			$this->getService('014'),
			$this->getService('011'),
			$this->getService('02'),
			$this->getService('05'),
		);
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('01'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectAuthorizationFacade($this->getService('016'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__10(): App\Presentation\Sign\SignPresenter
	{
		$service = new App\Presentation\Sign\SignPresenter;
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('01'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectAuthorizationFacade($this->getService('016'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__11(): App\Presentation\Tag\TagPresenter
	{
		$service = new App\Presentation\Tag\TagPresenter($this->getService('014'), $this->getService('08'), $this->getService('07'));
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('01'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectAuthorizationFacade($this->getService('016'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__12(): App\Presentation\User\UserPresenter
	{
		$service = new App\Presentation\User\UserPresenter($this->getService('08'), $this->getService('011'));
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('01'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectAuthorizationFacade($this->getService('016'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__13(): NetteModule\ErrorPresenter
	{
		return new NetteModule\ErrorPresenter($this->getService('tracy.logger'));
	}


	public function createServiceApplication__14(): NetteModule\MicroPresenter
	{
		return new NetteModule\MicroPresenter($this, $this->getService('http.request'), $this->getService('01'));
	}


	public function createServiceApplication__2(): App\Presentation\Author\AuthorPresenter
	{
		$service = new App\Presentation\Author\AuthorPresenter($this->getService('09'), $this->getService('08'), $this->getService('03'));
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('01'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectAuthorizationFacade($this->getService('016'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__3(): App\Presentation\Category\CategoryPresenter
	{
		$service = new App\Presentation\Category\CategoryPresenter(
			$this->getService('010'),
			$this->getService('08'),
			$this->getService('014'),
			$this->getService('04'),
		);
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('01'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectAuthorizationFacade($this->getService('016'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__4(): App\Presentation\Dashboard\DashboardPresenter
	{
		$service = new App\Presentation\Dashboard\DashboardPresenter(
			$this->getService('013'),
			$this->getService('08'),
			$this->getService('010'),
			$this->getService('09'),
			$this->getService('011'),
			$this->getService('014'),
		);
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('01'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectAuthorizationFacade($this->getService('016'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__5(): App\Presentation\Error\Error4xx\Error4xxPresenter
	{
		$service = new App\Presentation\Error\Error4xx\Error4xxPresenter;
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('01'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__6(): App\Presentation\Error\Error5xx\Error5xxPresenter
	{
		return new App\Presentation\Error\Error5xx\Error5xxPresenter($this->getService('tracy.logger'));
	}


	public function createServiceApplication__7(): App\Presentation\Home\HomePresenter
	{
		$service = new App\Presentation\Home\HomePresenter($this->getService('08'), $this->getService('010'), $this->getService('014'));
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('01'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__8(): App\Presentation\Review\ReviewPresenter
	{
		$service = new App\Presentation\Review\ReviewPresenter($this->getService('011'), $this->getService('08'), $this->getService('05'));
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('01'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectAuthorizationFacade($this->getService('016'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__9(): App\Presentation\Search\SearchPresenter
	{
		$service = new App\Presentation\Search\SearchPresenter(
			$this->getService('012'),
			$this->getService('010'),
			$this->getService('014'),
			$this->getService('06'),
		);
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('01'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectAuthorizationFacade($this->getService('016'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__application(): Nette\Application\Application
	{
		$service = new Nette\Application\Application(
			$this->getService('application.presenterFactory'),
			$this->getService('01'),
			$this->getService('http.request'),
			$this->getService('http.response'),
		);
		Nette\Bridges\ApplicationDI\ApplicationExtension::initializeBlueScreenPanel(
			$this->getService('tracy.blueScreen'),
			$service,
		);
		$this->getService('tracy.bar')->addPanel(new Nette\Bridges\ApplicationTracy\RoutingPanel(
			$this->getService('01'),
			$this->getService('http.request'),
			$this->getService('application.presenterFactory'),
		));
		return $service;
	}


	public function createServiceApplication__linkGenerator(): Nette\Application\LinkGenerator
	{
		return new Nette\Application\LinkGenerator(
			$this->getService('01'),
			$this->getService('http.request')->getUrl()->withoutUserInfo(),
			$this->getService('application.presenterFactory'),
		);
	}


	public function createServiceApplication__presenterFactory(): Nette\Application\IPresenterFactory
	{
		$service = new Nette\Application\PresenterFactory(new Nette\Bridges\ApplicationDI\PresenterFactoryCallback(
			$this,
			5,
			'C:\xampp\htdocs\eclipse/temp/cache/nette.application/touch',
		));
		$service->setMapping(['*' => 'App\Presentation\*\**Presenter']);
		return $service;
	}


	public function createServiceAuthorFactory(): App\Factory\AuthorFactory
	{
		return new App\Factory\AuthorFactory;
	}


	public function createServiceAuthorFactoryAlias(): App\Factory\Interface\IAuthorFactory
	{
		return $this->getService('authorFactory');
	}


	public function createServiceAuthorRepository(): App\Repository\AuthorRepository
	{
		return new App\Repository\AuthorRepository($this->getService('database.default.explorer'));
	}


	public function createServiceAuthorRepositoryAlias(): App\Repository\Interface\IAuthorRepository
	{
		return $this->getService('authorRepository');
	}


	public function createServiceAuthorService(): App\Service\AuthorService
	{
		return new App\Service\AuthorService($this->getService('authorRepository'), $this->getService('authorFactory'));
	}


	public function createServiceAuthorServiceAlias(): App\Service\IAuthorService
	{
		return $this->getService('authorService');
	}


	public function createServiceCache__journal(): Nette\Caching\Storages\Journal
	{
		return new Nette\Caching\Storages\SQLiteJournal('C:\xampp\htdocs\eclipse/temp/cache/journal.s3db');
	}


	public function createServiceCache__storage(): Nette\Caching\Storage
	{
		return new Nette\Caching\Storages\FileStorage('C:\xampp\htdocs\eclipse/temp/cache', $this->getService('cache.journal'));
	}


	public function createServiceCategoryFactory(): App\Factory\CategoryFactory
	{
		return new App\Factory\CategoryFactory;
	}


	public function createServiceCategoryFactoryAlias(): App\Factory\Interface\ICategoryFactory
	{
		return $this->getService('categoryFactory');
	}


	public function createServiceCategoryRepository(): App\Repository\CategoryRepository
	{
		return new App\Repository\CategoryRepository($this->getService('database.default.explorer'));
	}


	public function createServiceCategoryRepositoryAlias(): App\Repository\Interface\ICategoryRepository
	{
		return $this->getService('categoryRepository');
	}


	public function createServiceCategoryService(): App\Service\CategoryService
	{
		return new App\Service\CategoryService($this->getService('categoryRepository'), $this->getService('categoryFactory'));
	}


	public function createServiceCategoryServiceAlias(): App\Service\ICategoryService
	{
		return $this->getService('categoryService');
	}


	public function createServiceContainer(): Nette\DI\Container
	{
		return $this;
	}


	public function createServiceDatabase__default__connection(): Nette\Database\Connection
	{
		$service = new Nette\Database\Connection('mysql:host=localhost;dbname=eclipse', /*sensitive{*/'root'/*}*/, null, []);
		Nette\Bridges\DatabaseTracy\ConnectionPanel::initialize(
			$service,
			true,
			'default',
			true,
			$this->getService('tracy.bar'),
			$this->getService('tracy.blueScreen'),
		);
		return $service;
	}


	public function createServiceDatabase__default__conventions(): Nette\Database\Conventions\DiscoveredConventions
	{
		return new Nette\Database\Conventions\DiscoveredConventions($this->getService('database.default.structure'));
	}


	public function createServiceDatabase__default__explorer(): Nette\Database\Explorer
	{
		return new Nette\Database\Explorer(
			$this->getService('database.default.connection'),
			$this->getService('database.default.structure'),
			$this->getService('database.default.conventions'),
			$this->getService('cache.storage'),
		);
	}


	public function createServiceDatabase__default__structure(): Nette\Database\Structure
	{
		return new Nette\Database\Structure($this->getService('database.default.connection'), $this->getService('cache.storage'));
	}


	public function createServiceHttp__request(): Nette\Http\Request
	{
		return $this->getService('http.requestFactory')->fromGlobals();
	}


	public function createServiceHttp__requestFactory(): Nette\Http\RequestFactory
	{
		$service = new Nette\Http\RequestFactory;
		$service->setProxy([]);
		return $service;
	}


	public function createServiceHttp__response(): Nette\Http\Response
	{
		$service = new Nette\Http\Response;
		$service->cookieSecure = $this->getService('http.request')->isSecured();
		return $service;
	}


	public function createServiceLatte__latteFactory(): Nette\Bridges\ApplicationLatte\LatteFactory
	{
		return new class ($this) implements Nette\Bridges\ApplicationLatte\LatteFactory {
			public function __construct(
				private Container_5520ba1493 $container,
			) {
			}


			public function create(): Latte\Engine
			{
				$service = new Latte\Engine;
				$service->setTempDirectory('C:\xampp\htdocs\eclipse/temp/cache/latte');
				$service->setAutoRefresh(true);
				$service->setStrictTypes(true);
				$service->setStrictParsing(true);
				$service->enablePhpLinter(null);
				$service->setLocale(null);
				func_num_args() && $service->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(func_get_arg(0)));
				$service->addExtension(new Nette\Bridges\CacheLatte\CacheExtension($this->container->getService('cache.storage')));
				$service->addExtension(new Nette\Bridges\FormsLatte\FormsExtension);
				$service->addExtension(new App\Presentation\Accessory\LatteExtension);
				return $service;
			}
		};
	}


	public function createServiceLatte__templateFactory(): Nette\Bridges\ApplicationLatte\TemplateFactory
	{
		$service = new Nette\Bridges\ApplicationLatte\TemplateFactory(
			$this->getService('latte.latteFactory'),
			$this->getService('http.request'),
			$this->getService('security.user'),
			$this->getService('cache.storage'),
			null,
		);
		Nette\Bridges\ApplicationDI\LatteExtension::initLattePanel($service, $this->getService('tracy.bar'), false);
		return $service;
	}


	public function createServiceMail__mailer(): Nette\Mail\Mailer
	{
		return new Nette\Mail\SendmailMailer;
	}


	public function createServiceReviewFactory(): App\Factory\ReviewFactory
	{
		return new App\Factory\ReviewFactory;
	}


	public function createServiceReviewFactoryAlias(): App\Factory\Interface\IReviewFactory
	{
		return $this->getService('reviewFactory');
	}


	public function createServiceReviewRepository(): App\Repository\ReviewRepository
	{
		return new App\Repository\ReviewRepository(
			$this->getService('database.default'),
			$this->getService('addonRepository'),
			$this->getService('cache.storage'),
		);
	}


	public function createServiceReviewRepositoryAlias(): App\Repository\Interface\IReviewRepository
	{
		return $this->getService('reviewRepository');
	}


	public function createServiceReviewService(): App\Service\ReviewService
	{
		return new App\Service\ReviewService($this->getService('reviewRepository'), $this->getService('reviewFactory'));
	}


	public function createServiceReviewServiceAlias(): App\Service\IReviewService
	{
		return $this->getService('reviewService');
	}


	public function createServiceScreenshotFactory(): App\Factory\ScreenshotFactory
	{
		return new App\Factory\ScreenshotFactory;
	}


	public function createServiceScreenshotFactoryAlias(): App\Factory\Interface\IScreenshotFactory
	{
		return $this->getService('screenshotFactory');
	}


	public function createServiceSearchService(): App\Service\SearchService
	{
		return new App\Service\SearchService(
			$this->getService('addonRepository'),
			$this->getService('authorRepository'),
			$this->getService('tagRepository'),
			$this->getService('categoryRepository'),
		);
	}


	public function createServiceSearchServiceAlias(): App\Service\ISearchService
	{
		return $this->getService('searchService');
	}


	public function createServiceSecurity__passwords(): Nette\Security\Passwords
	{
		return new Nette\Security\Passwords;
	}


	public function createServiceSecurity__user(): Nette\Security\User
	{
		$service = new Nette\Security\User($this->getService('security.userStorage'));
		$this->getService('tracy.bar')->addPanel(new Nette\Bridges\SecurityTracy\UserPanel($service));
		return $service;
	}


	public function createServiceSecurity__userStorage(): Nette\Security\UserStorage
	{
		return new Nette\Bridges\SecurityHttp\SessionStorage($this->getService('session.session'));
	}


	public function createServiceSession__session(): Nette\Http\Session
	{
		$service = new Nette\Http\Session($this->getService('http.request'), $this->getService('http.response'));
		$service->setOptions(['cookieSamesite' => 'Lax']);
		return $service;
	}


	public function createServiceStatisticsService(): App\Service\StatisticsService
	{
		return new App\Service\StatisticsService(
			$this->getService('addonRepository'),
			$this->getService('authorRepository'),
			$this->getService('categoryRepository'),
			$this->getService('reviewRepository'),
			$this->getService('database.default'),
		);
	}


	public function createServiceStatisticsServiceAlias(): App\Service\IStatisticsService
	{
		return $this->getService('statisticsService');
	}


	public function createServiceTagFactory(): App\Factory\TagFactory
	{
		return new App\Factory\TagFactory;
	}


	public function createServiceTagFactoryAlias(): App\Factory\Interface\ITagFactory
	{
		return $this->getService('tagFactory');
	}


	public function createServiceTagRepository(): App\Repository\TagRepository
	{
		return new App\Repository\TagRepository($this->getService('database.default.explorer'));
	}


	public function createServiceTagRepositoryAlias(): App\Repository\Interface\ITagRepository
	{
		return $this->getService('tagRepository');
	}


	public function createServiceTagService(): App\Service\TagService
	{
		return new App\Service\TagService($this->getService('tagRepository'), $this->getService('tagFactory'));
	}


	public function createServiceTagServiceAlias(): App\Service\ITagService
	{
		return $this->getService('tagService');
	}


	public function createServiceTracy__bar(): Tracy\Bar
	{
		return Tracy\Debugger::getBar();
	}


	public function createServiceTracy__blueScreen(): Tracy\BlueScreen
	{
		return Tracy\Debugger::getBlueScreen();
	}


	public function createServiceTracy__logger(): Tracy\ILogger
	{
		return Tracy\Debugger::getLogger();
	}


	public function initialize(): void
	{
		// di.
		(function () {
			$this->getService('tracy.bar')->addPanel(new Nette\Bridges\DITracy\ContainerPanel($this));
		})();
		// http.
		(function () {
			$response = $this->getService('http.response');
			$response->setHeader('X-Powered-By', 'Nette Framework 3');
			$response->setHeader('Content-Type', 'text/html; charset=utf-8');
			$response->setHeader('X-Frame-Options', 'SAMEORIGIN');
			Nette\Http\Helpers::initCookie($this->getService('http.request'), $response);
		})();
		// session.
		(function () {
			$this->getService('session.session')->autoStart(false);
		})();
		// tracy.
		(function () {
			if (!Tracy\Debugger::isEnabled()) { return; }
			$logger = $this->getService('tracy.logger');
			if ($logger instanceof Tracy\Logger) $logger->mailer = [
				new Tracy\Bridges\Nette\MailSender(
					$this->getService('mail.mailer'),
					null,
					$this->getByType('Nette\Http\Request', false)?->getUrl()->getHost(),
				),
				'send',
			];
		})();
	}
}
