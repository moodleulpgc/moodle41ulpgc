<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Geometry
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Collidable.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * Interface to check for collisions between geometries
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Geometry
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Core_Geometry_Collidable
{
    /**
     * Checks if this geometry collides with another geometry.
     *
     * @param SetaPDF_Core_Geometry_Collidable $geometry
     * @return bool
     */
    public function collides(SetaPDF_Core_Geometry_Collidable $geometry);
}