<?php
/**
 * @copyright Copyright (C) 2015-2018 AIZAWA Hina
 * @license https://github.com/fetus-hina/stat.ink/blob/master/LICENSE MIT
 * @author AIZAWA Hina <hina@bouhime.com>
 */

declare(strict_types=1);

namespace statink\yii2\ipBadge;

use Yii;
use statink\yii2\ipBadge\assets\AssetBundle;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class IpBadgeWidget extends Widget
{
    public $options = [
        'tag' => 'span',
        'class' => [
            'via-badge',
            'via-badge-ipv{version}',
        ],
    ];
    public $innerOptions = [
        'tag' => 'span',
        'class' => [
            'via-ip-version',
        ],
    ];

    public function run()
    {
        $version = $this->getIpVersion();
        if ($version === null) {
            return '';
        }
        
        AssetBundle::register($this->view);

        return $this->decorate($this->renderBadge($version), $version);
    }

    protected function renderBadge(int $version): string
    {
        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'span');
        return Html::tag(
            $tag,
            $this->renderInnerBadge($version),
            array_merge(['id' => $this->id], $options)
        );
    }

    protected function renderInnerBadge(int $version): string
    {
        $options = $this->innerOptions;
        $tag = ArrayHelper::remove($options, 'tag', 'span');
        return Html::tag(
            $tag,
            Html::encode('via IPv{version}'),
            $options
        );
    }

    protected function decorate(string $html, int $ipVersion): string
    {
        return str_replace('{version}', (string)$ipVersion, $html);
    }

    public function getIpVersion(): ?int
    {
        $ipAddr = (string)(Yii::$app->request->userIP ?? '');
        if (preg_match('/^[0-9.]+$/', $ipAddr)) {
            return 4;
        } elseif (preg_match('/^[0-9a-fA-F:]+$/', $ipAddr)) {
            return 6;
        } else {
            return null;
        }
    }
}
