<?php

declare(strict_types=1);

/*
 * This file is part of JohnCMS Content Management System.
 *
 * @copyright JohnCMS Community
 * @license   https://opensource.org/licenses/GPL-3.0 GPL-3.0
 * @link      https://johncms.com JohnCMS Project
 */

use Doc\Pickup;
use Doc\Request;

defined('_IN_JOHNCMS') || die('Error: restricted access');

/**
 * @var PDO                        $db
 * @var Johncms\Api\ToolsInterface $tools
 * @var Johncms\Api\UserInterface  $user
 * @var League\Plates\Engine       $view
 * @var \Johncms\Utility\NavChain  $nav_chain
 */

// Получаем данные POST запроса
$post = Request::make()->postData();

if ($user->rights >= Request::ROLE) {
    // Добавление документации
    if (isset($post['article_submit'])) {
        $article_name = trim($post['article_name'] ?? '');
        $article_description = trim($post['article_description'] ?? '');
        $cms_version = trim($post['cms_version'] ?? '');
        $parent_id = abs((int)($post['parent_id'] ?? ''));
        $status = abs((int)($post['status'] ?? ''));
        $text = trim($post['text'] ?? '');
        $date_of_creation = (new DateTime())->getTimestamp();

        $error = [];

        $article_name ?: $error[] = __('You did not enter an article title');
        $article_description ?: $error[] = __('You did not enter an article description');
        $cms_version ?: $error[] = __('You have not entered a version JohnCMS');
        $status ?: $error[] = __('You have not entered article status');
        ($status <= 9 && $status >= 0) ?: $error[] = __('You entered an invalid status value');
        $text ?: $error[] = __('You have not entered the text of the article');

        if ( ($flood = $tools->antiflood()) ) {
            $error[] = sprintf(__('You cannot add the message so often. Please, wait %d seconds.'), $flood);
        }

        if (! $error) {
            $db->prepare('
                INSERT INTO `documentation_articles` SET
                `name` = ?,
                `description` = ?,
                `parent_id` = ?,
                `cms_version` = ?,
                `status` = ?,
                `text` = ?,
                `author` = ?,
                `date_of_creation` = ?
            ')->execute([
                $article_name,
                $article_description,
                $parent_id,
                $cms_version,
                $status,
                $text,
                $user->name,
                $date_of_creation
            ]);

            // Добавляем элементы к навигационной цепочке
            Pickup::Breadcrumbs($nav_chain, [
                __('Documentation') => './',
                __('Add article')
            ]);

            echo $view->render("$namespace::result", [
                'title'    => __('Adding an article'),
                'message'  => __('Article added'),
                'type'     => 'success',
                'back_url' => '/documentation/?articles=' . $parent_id
            ]);
        } else {
            echo $view->render("$namespace::result", [
                'title'    => __('Adding an article'),
                'message'  => join('<br>', $error),
                'type'     => 'error',
                'back_url' => '/documentation/?add-article'
            ]);
        }
    }

    // Добавление раздела
    if (isset($post['section_submit'])) {
        $parent_id = abs((int)($post['parent_id'] ?? ''));
        $section_name = trim($post['section_name'] ?? '');
        $section_description = trim($post['section_description'] ?? '');
        $sort = abs((int)($post['sort'] ?? ''));

        $error = [];

        $parent_id ?: $error[] = __('You did not enter a section binding');
        ($parent_id <= 100 && $parent_id >= 1) ?: $error[] = __('You entered an invalid binding value');
        $section_name ?: $error[] = __('You did not enter a section title');
        $section_description ?: $error[] = __('You did not enter a section description');
        $sort ?: $error[] = __('You did not enter a value to sort');
        ($sort <= 100 && $sort >= 0) ?: $error[] = __('You entered an invalid sort value');

        if ( ($flood = $tools->antiflood()) ) {
            $error[] = sprintf(__('You cannot add the message so often. Please, wait %d seconds.'), $flood);
        }

        if (! $error) {
            $db->prepare('
                INSERT INTO `documentation_sections` SET
                `parent_id` = ?,
                `name` = ?,
                `description` = ?,
                `sort` = ?
            ')->execute([
                $parent_id,
                $section_name,
                $section_description,
                $sort
            ]);

            // Добавляем элементы к навигационной цепочке
            Pickup::Breadcrumbs($nav_chain, [
                __('Documentation') => './',
                __('Adding a section')
            ]);

            echo $view->render("$namespace::result", [
                'title'    => __('Adding a section'),
                'message'  => __('Section added'),
                'type'     => 'success',
                'back_url' => '/documentation/',
            ]);
        } else {
            echo $view->render("$namespace::result", [
                'title'    => __('Adding a section'),
                'message'  => join('<br>', $error),
                'type'     => 'error',
                'back_url' => '/documentation/?add-section'
            ]);
        }
    }
} else {
    pageNotFound();
}
