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
 * @var PDO                       $db
 * @var Johncms\Api\UserInterface $user
 * @var League\Plates\Engine      $view
 */

// Получаем данные post-запроса
$post = Request::make()->postData();
$id = (int) $post['id'] ?? 0;

if ($user->rights >= Request::ROLE && $uri === 'del') {
    // Удаление раздела
    if (isset($post['delete_section']) && $id) {
        // Добавляем элементы к навигационной цепочке
        Pickup::Breadcrumbs($nav_chain, [
            __('Documentation') => './',
            __('Delete section')
        ]);

        $db->query("DELETE FROM `documentation_sections` WHERE `parent_id` = {$id};");
        $db->query("DELETE FROM `documentation_articles` WHERE `parent_id` = {$id};");

        echo $view->render("$namespace::result", [
            'title'    => __('Delete'),
            'message'  => __('Section deleted'),
            'type'     => 'success',
            'back_url' => '/documentation/',
        ]);
    }

    // Удаление статьи
    if (isset($post['delete_article']) && $id) {
        // Добавляем элементы к навигационной цепочке
        Pickup::Breadcrumbs($nav_chain, [
            __('Documentation') => './',
            __('Delete article')
        ]);

        $db->query("DELETE FROM `documentation_articles` WHERE `id` = {$id}");
        echo $view->render("$namespace::result", [
            'title'    => __('Delete'),
            'message'  => __('Article deleted'),
            'type'     => 'success',
            'back_url' => '/documentation/?articles=' . $id,
        ]);
    }
} else {
    pageNotFound();
}
