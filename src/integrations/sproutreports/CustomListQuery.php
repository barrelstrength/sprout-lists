<?php

namespace barrelstrength\sproutlists\integrations\sproutreports\datasources;

use barrelstrength\sproutbasereports\base\SegmentDataSource;
use barrelstrength\sproutbasereports\elements\Report;
use Craft;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class CustomListQuery
 *
 * @package Craft
 *
 * @property string $name
 */
class CustomListQuery extends SegmentDataSource
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-reports', 'Custom List Query');
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return Craft::t('sprout-reports', 'Create a list using a custom database query');
    }

    /**
     * @inheritdoc
     */
    public function getResults(Report $report, array $settings = []): array
    {
        $query = $report->getSetting('query');

        $result = [];

        try {
            $result = Craft::$app->getDb()->createCommand($query)->queryAll();
        } catch (Exception $e) {
            $report->setResultsError($e->getMessage());
        }

        return $result;
    }

    /**
     * @param array $settings
     *
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSettingsHtml(array $settings = [])
    {
        $settingsErrors = $this->report->getErrors('settings');
        $settingsErrors = array_shift($settingsErrors);

        return Craft::$app->getView()->renderTemplate('sprout-base-reports/_components/datasources/CustomQuery/settings', [
            'settings' => count($settings) ? $settings : $this->report->getSettings(),
            'errors' => $settingsErrors
        ]);
    }

    /**
     * @inheritdoc
     */
    public function validateSettings(array $settings = [], array &$errors = []): bool
    {
        if (empty($settings['query'])) {
            $errors['query'][] = Craft::t('sprout-reports', 'Query cannot be blank.');

            return false;
        }

        return true;
    }
}
