<?php

/**
 * @file
 * Contains \Drupal\quiz\questionListBuilder.
 */

namespace Drupal\quiz;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Question entities.
 *
 * @ingroup question
 */
class QuestionListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;

  protected $qids;
  protected $quiz;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Question ID');
    $header['name'] = $this->t('Name');
    $header['type'] = $this->t('Type');
    return $header + parent::buildHeader();
  }

  public function setQuiz($quizId) {
    $this->quiz = $quizId;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\quiz\Entity\question */
    $row['id'] = $entity->id();

    //ugly and deprecated. Please fix.
    $row['name'] = $this->l(
      $this->getLabel($entity),
      new Url(
        'entity.question.edit_form', array(
          'question' => $entity->id(),
        )
      )
    );

    $row['type'] = $entity->bundle();


    $row['operations']['data']['#type'] = 'operations';
    $row['operations']['data']['#links']['remove']['title'] = 'Remove';
    $row['operations']['data']['#links']['remove']['url'] = Url::fromRoute('entity.quiz.remove_question', ['quiz' => $this->quiz, 'question' => $entity->id()]);
    return $row;

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function setIds($qids) {
    $this->qids = $qids;
  }

  public function getIds() {
    return $this->qids;
  }

  protected function getEntities() {
    $qids = $this->getIds();
    return $this->storage->loadMultiple($qids);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = array(
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#title' => $this->getTitle(),
      '#rows' => array(),
      '#empty' => $this->t('There is no @label yet.', array('@label' => $this->entityType->getLabel())),
      '#cache' => [
        'contexts' => $this->entityType->getListCacheContexts(),
        'tags' => $this->entityType->getListCacheTags(),
      ],
    );

    foreach ($this->getEntities() as $entity) {
      if ($row = $this->buildRow($entity)) {
        $build['table']['#rows'][$entity->id()] = $row;
      }
    }

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $build['pager'] = array(
        '#type' => 'pager',
      );
    }
    return $build;
  }
}
