<?php

/**
 * This file is part of JohnCMS Content Management System.
 *
 * @copyright JohnCMS Community
 * @license   https://opensource.org/licenses/GPL-3.0 GPL-3.0
 * @link      https://johncms.com JohnCMS Project
 */

declare(strict_types=1);

use Johncms\System\Legacy\Tools;
use Johncms\System\Users\User;
use Johncms\System\View\Render;
use Johncms\NavChain;
use Johncms\System\i18n\Translator;

use Doc\Request;
use Doc\Pickup;

defined('_IN_JOHNCMS') || die('Error: restricted access');

/**
 * @var PDO $db
 * @var Tools $tools
 * @var User $user
 * @var Render $view
 * @var NavChain $nav_chain
 */

$db = di(PDO::class);
$tools = di(Tools::class);
$user = di(User::class);
$view = di(Render::class);
$nav_chain = di(NavChain::class);
$route = di('route');

// Регистрируем Namespace для шаблонов модуля
$namespace = 'Doc';
$view->addFolder($namespace, __DIR__ . '/templates/');

// Регистрируем папку с языками модуля
di(Translator::class)->addTranslationDomain('documentation', __DIR__ . '/locale');

// Регистрируем автозагрузчик для классов модуля
$loader = new Aura\Autoload\Loader;
$loader->register();
$loader->addPrefix($namespace, __DIR__ . '/classes');

// Получаем данные GET запроса
$uri = Request::make()->getData();

// TODO: Записать условие в Request
if (strpos($uri, '=') !== false) {
    [$uri, $id] = explode('=', $uri, 2);
    $id = abs((int) $id);
} else {
    $id = 0;
}

$actions = [
    'add',
    'del',
    'edit',
];

if (($key = array_search($uri, $actions)) !== false) {
    require __DIR__ . '/includes/' . $actions[$key] . '.php';
} else {
    $options = [];

    switch ($uri) {

        // Добавление раздела
        case 'add-section':
            $chains = [
                __('Documentation sections') => './',
                __('Add section')
            ];
            $template = "$namespace::add_section";
            break;

        // Добавление документации
        case 'add-article':
            $chains = [
                __('Documentation sections') => './',
                __('Add article')
            ];
            $template = "$namespace::add_article";

            $options = [
                'parent_id' => (int) $id
            ];
            break;

        // Редактирование раздела
        case 'edit-section':
            $chains = [
                __('Documentation sections') => './',
                __('Edit section')
            ];
            $template = "$namespace::edit_section";

            $id = (int) $id;
            $req = $db->query("SELECT * FROM `documentation_sections` WHERE `id` = {$id}")->fetch();
            $options = [
                'id' => $id,
                'parent_id' => $req['parent_id'],
                'name' => $req['name'],
                'description' => $req['description'],
                'sort' => $req['sort']
            ];
            break;

        // Редактирование документации
        case 'edit-article':
            $chains = [
                __('Documentation sections') => './',
                __('Edit article')
            ];
            $template = "$namespace::edit_article";

            $id = (int) $id;
            $req = $db->query("SELECT * FROM `documentation_articles` WHERE `id` = {$id}")->fetch();
            $options = [
                'id' => $id,
                'name' => $req['name'],
                'description' => $req['description'],
                'parent_id' => $req['parent_id'],
                'status' => $req['status'],
                'author' => $req['author'],
                'cms_version' => $req['cms_version'],
                'text' => $req['text']
            ];
            break;

        // Удаление раздела
        case 'delete-section':
            $chains = [
                __('Documentation sections') => './',
                __('Delete section')
            ];
            $template = "$namespace::delete_section";

            $options = [
                'id' => (int) $id,
            ];
            break;

        // Удаление документации
        case 'delete-article':
            $chains = [
                __('Documentation sections') => './',
                __('Delete article')
            ];
            $template = "$namespace::delete_article";

            $options = [
                'id' => (int) $id,
                'parent_id' => (int) $id,
            ];
            break;

        // Отображение статей документации
        case 'articles':
            $chains = [
                __('Documentation sections') => './',
                __('View documentation')
            ];
            $template = "$namespace::articles";

            $id = (int) $id;
            $total = $db->query("
                SELECT COUNT(*) FROM `documentation_articles`
                WHERE `parent_id` = {$id} 
            ")->fetchColumn();

            $req = $db->query("
                SELECT * FROM `documentation_articles` 
                WHERE `parent_id` = {$id} 
                ORDER BY `date_of_creation` DESC LIMIT {$start}, " . $user->config->kmess
            );
            $options = [
                'parent_id' => $id,
                'pagination' => $tools->displayPagination('?', $start, $total, $user->config->kmess),
                'total' => $total,
                'list' => function () use ($req): Generator {
                    while ($res = $req->fetch()) {
                        yield $res;
                    }
                }
            ];
            break;

        // Отображение разделов документации
        default:
            $chains = __('Documentation sections');
            $template = "$namespace::index";

            $total = $db->query('SELECT COUNT(*) FROM `documentation_sections`')->fetchColumn();
            $req = $db->query("
                SELECT * FROM `documentation_sections`
                ORDER BY `sort` ASC LIMIT {$start}, " . $user->config->kmess
            );
            $options = [
                'pagination' => $tools->displayPagination('?', $start, $total, $user->config->kmess),
                'total' => $total,
                'list' => function () use ($req): Generator {
                    while ($res = $req->fetch()) {
                        yield $res;
                    }
                }
            ];
    }

    // Добавляем элементы к навигационной цепочке
    Pickup::Breadcrumbs($nav_chain, $chains);

    // Формируем html-страницу
    echo $view->render($template, $options);
}
