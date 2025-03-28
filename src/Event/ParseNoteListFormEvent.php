<?php

/**
 * This file is part of MetaModels/notelist.
 *
 * (c) 2017-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2017-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\NoteListBundle\Event;

use MetaModels\IMetaModel;
use MetaModels\Render\Setting\ICollection;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is triggered when a note list form element is rendered in the frontend.
 */
class ParseNoteListFormEvent extends Event
{
    /**
     * The MetaModel.
     *
     * @var IMetaModel
     */
    private IMetaModel $metaModel;

    /**
     * The form renderer.
     *
     * @var ICollection
     */
    private ICollection $renderSetting;

    /**
     * The note list id.
     *
     * @var string
     */
    private string $noteListId;

    /**
     * Create a new instance.
     *
     * @param IMetaModel  $metaModel     The MetaModel.
     * @param ICollection $renderSetting The renderer.
     * @param string      $noteListId    The note list.
     */
    public function __construct(IMetaModel $metaModel, ICollection $renderSetting, string $noteListId)
    {
        $this->renderSetting = $renderSetting;
        $this->noteListId    = $noteListId;
        $this->metaModel     = $metaModel;
    }

    /**
     * Retrieve metaModel.
     *
     * @return IMetaModel
     */
    public function getMetaModel()
    {
        return $this->metaModel;
    }

    /**
     * Retrieve renderer.
     *
     * @return ICollection
     */
    public function getRenderSetting()
    {
        return $this->renderSetting;
    }

    /**
     * Retrieve noteListId.
     *
     * @return string
     */
    public function getNoteListId()
    {
        return $this->noteListId;
    }
}
