<?php

namespace Kanboard\Plugin\Telegram\Helper;

use Kanboard\Core\Base;
use Kanboard\Model\UserModel;

class TelegramHelper extends Base {


  /**
   * Converts user mentions and task IDs in the content to corresponding links for Telegram or Kanboard.
   *
   * @param string $content  The content string to be processed
   * @param array  $project  The project context
   *
   * @return string The modified content string with user mentions and task IDs converted to links
   */
  public function processContent($content, $project) {
    $usernamePattern = '/@([a-zA-Z0-9_-]+)/';
    $taskPattern = '!#(\d+)!i';

    $UsernameReplacement = function ($matches) {
      return $this->inlineUserLink($matches[1]);
    };

    $taskReplacement = function ($matches) use ($project) {
      return $this->inlineTaskLink($matches[1], $project);
    };

    $content = preg_replace_callback($usernamePattern, $UsernameReplacement, $content);
    $content = preg_replace_callback($taskPattern, $taskReplacement, $content);
    return $content;
  }

  /**
   * Generates an inline link for a user mention in Telegram or Kanboard user profile link.
   *
   * @param string $username The username of the user to be mentioned
   *
   * @return string The generated link or username if the user is not found
   */
  public function inlineUserLink($username) {
    $userModel = new UserModel($this->container);
    $user = $userModel->getByUsername($username);

    if (!$user) {
      return '@' . $username;
    }

    $have_chat_id = $this->userMetadataModel->exists($user['id'], 'telegram_user_cid');
    $have_chat_id = $have_chat_id ? $this->userMetadataModel->get($user['id'], 'telegram_user_cid') : false;
    $name = $user['name'] ?: '@' . $user['username'];

    if ($have_chat_id) {
      $chat_id = $this->userMetadataModel->get($user['id'], 'telegram_user_cid');
      return '<a href="tg://user?id=' . $chat_id . '">' . $name . '</a>';
    }

    $url =  $this->helper->url->to('UserViewController', 'profile', array('user_id' => $user['id']), '', true);
    return '<a href="' . $url . '">' . $name . '</a>';
  }

  /**
   * Generates an inline link for a task ID in Kanboard.
   *
   * @param int    $task_id  The task ID to be linked
   * @param array  $project  The project context
   *
   * @return string The generated link for the task ID
   */
  public function inlineTaskLink($task_id, $project) {
    $task_url = $this->helper->url->to('TaskViewController', 'show', array('task_id' => $task_id, 'project_id' => $project['id']), '', true);
    return '<a href="' . $task_url . '">#' . $task_id . '</a>';
  }

  /**
   * Cleans a string by replacing spaces with hyphens and removing special characters.
   *
   * @param string $string The input string to be cleaned
   *
   * @return string The cleaned string
   */
  public function clean($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    return preg_replace('/[^A-Za-z0-9\-.]/', '', $string); // Removes special chars.
  }
}
