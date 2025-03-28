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
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\NoteListBundle\Storage;

use Contao\Session;

/**
 * This class is the implementation of the Contao session storage.
 *
 * @SuppressWarnings(PHPMD.DeprecatedClass)
 */
class ContaoSessionAdapter implements AdapterInterface
{
    /**
     * The contao session.
     *
     * @var Session
     *
     * @psalm-suppress DeprecatedClass
     */
    private Session $session;

    /**
     * Initialize the object.
     *
     * @param Session $session The session to work on.
     *
     * @psalm-suppress DeprecatedClass
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey(string $key): array
    {
        if (
            null !== ($value = $this->session->get('metamodel_notelists'))
            && \array_key_exists('metamodel_notelist_' . $key, $value)
        ) {
            return (array) $value['metamodel_notelist_' . $key];
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function setKey(string $key, array $value): void
    {
        $this->session->set(
            'metamodel_notelists',
            \array_merge(
                (array) $this->session->get('metamodel_notelists'),
                ['metamodel_notelist_' . $key => $value]
            )
        );
    }
}
