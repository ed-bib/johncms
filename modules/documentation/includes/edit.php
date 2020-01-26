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
 */

// Добавляем элементы к навигационной цепочке
Pickup::Breadcrumbs($nav_chain, [
    __('Documentation') => './',
    __('Editing an article') => '?edit-article'
]);

// Получаем данные POST запроса
$post = Request::make()->postData();

if ($user->rights >= Request::ROLE) {
    // Редактирование статьи
    if (isset($post['article_submit'])) {
        $post = array_map('trim', $post);

        $id = abs((int)$post['id'] ?? '');
        $name = $post['name'] ?? '';
        $description = $post['description'] ?? '';
        $parent_id = $post['parent_id'] ?? '';
        $status = abs((int)$post['status'] ?? '');
        $author = $post['author'] ?? '';
        $cms_version = $post['cms_version'] ?? '';
        $text = $post['text'] ?? '';
        $date_of_change = (new DateTime())->getTimestamp();

        $error = [];

        $id ?: $error[] = __('Wrong data');
        $name ?: $error[] = __('You have not entered articles title');
        $description ?: $error[] = __('You have not entered article description');
        $parent_id ?: $error[] = __('You have not enter a section number');
        $status ?: $error[] = __('You have not enter status');
        $author ?: $error[] = __('You have not enter article author');
        $cms_version ?: $error[] = __('You have not enter CMS version');
        $text ?: $error[] = __('You have not entered article text');

        if (! $error) {

            $db->prepare('
                UPDATE `documentation_articles` SET
                `name` = ?,
                `description` = ?,
                `parent_id` = ?,
                `status` = ?,
                `author` = ?,
                `cms_version` = ?,
                `text` = ?,
                `date_of_change` = ?
                WHERE `id` = ?
        ')->execute([
                $name,
                $description,
                $parent_id,
                $status,
                $author,
                $cms_version,
                $text,
                $date_of_change,
                $id
            ]);

            echo $view->render("$namespace::result", [
                'title'    => __('Article edit'),
                'message'  => __('Article changed'),
                'type'     => 'success',
                'back_url' => '/documentation/?articles=' . $parent_id,
            ]);

        } else {
            echo $view->render("$namespace::result", [
                'title'    => __('Article edit'),
                'message'  => $error,
                'type'     => 'error',
                'back_url' => '/documentation/?edit-article=' . $id,
            ]);
        }
    }

    // Редактирование раздела
    if (isset($post['section_submit'])) {
        $post = array_map('trim', $post);

        $id = abs((int)$post['id'] ?? '');
        $parent_id = $post['parent_id'] ?? '';
        $name = $post['name'] ?? '';
        $description = $post['description'] ?? '';
        $sort = abs((int)$post['sort'] ?? '');

        $error = [];

        $id ?: $error[] = __('Wrong data');
        $parent_id ?: $error[] = __('You have not enter a section number');
        $name ?: $error[] = __('You have not entered section title');
        $description ?: $error[] = __('You have not entered section description');
        $sort ?: $error[] = __('You have not enter a value to sort');

        if (! $error) {

            $db->prepare('
                UPDATE `documentation_sections` SET
                `parent_id` = ?,
                `name` = ?,
                `description` = ?,
                `sort` = ?
                WHERE `id` = ?
            ')->execute([
                $parent_id,
                $name,
                $description,
                $sort,
                $id
            ]);

            echo $view->render("$namespace::result", [
                'title'    => __('Section edit'),
                'message'  => __('Section changed'),
                'type'     => 'success',
                'back_url' => '/documentation/'
            ]);

        } else {
            echo $view->render("$namespace::result", [
                'title'    => __('Section edit'),
                'message'  => $error,
                'type'     => 'error',
                'back_url' => '/documentation/?edit-section=' . $id,
            ]);
        }
    }
} else {
    pageNotFound();
}
