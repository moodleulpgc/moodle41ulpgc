<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Draw.php 1580 2020-10-19 11:57:41Z maximilian.kresse $
 */

/**
 * A canvas helper class for draw operators
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Canvas
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Canvas_Draw extends SetaPDF_Core_Canvas_StandardOperators
{
    /**
     * Only fill style
     *
     * @var int
     */
    const STYLE_FILL = 1;

    /**
     * Only draw style
     *
     * @var int
     */
    const STYLE_DRAW = 2;

    /**
     * Draw and fill style
     *
     * @var int
     */
    const STYLE_DRAW_AND_FILL = 3;

    /**
     * Draws a line on the canvas.
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return SetaPDF_Core_Canvas_Draw
     */
    public function line($x1, $y1, $x2, $y2)
    {
        $this->_canvas->path()
            ->moveTo($x1, $y1)
            ->lineTo($x2, $y2)
            ->stroke();

        return $this;
    }

    /**
     * Draws a rectangle on the canvas.
     *
     * @param float $x1
     * @param float $y1
     * @param float $width
     * @param float $height
     * @param int $style
     * @return SetaPDF_Core_Canvas_Draw
     */
    public function rect($x1, $y1, $width, $height, $style = self::STYLE_DRAW)
    {
        $this->_canvas->path()
            ->rect($x1, $y1, $width, $height);
        $this->_drawStyle($style);

        return $this;
    }

    /**
     * Draws a circle on the canvas.
     *
     * @param float $x Abscissa of center.
     * @param float $y Ordinate of center.
     * @param float $r Radius.
     * @param int $style
     * @return SetaPDF_Core_Canvas_Draw
     */
    public function circle($x, $y, $r, $style = self::STYLE_DRAW)
    {
        return $this->ellipse($x, $y, $r, $r, $style);
    }

    /**
     * Draws an ellipse on the canvas.
     *
     * @param float $x Abscissa of center.
     * @param float $y Ordinate of center.
     * @param float $rx Horizontal radius.
     * @param float $ry Vertical radius.
     * @param int $style
     * @return SetaPDF_Core_Canvas_Draw
     */
    public function ellipse($x, $y, $rx, $ry, $style = self::STYLE_DRAW)
    {
        $lx = 4 / 3 * (M_SQRT2 - 1) * $rx;
        $ly = 4 / 3 * (M_SQRT2 - 1) * $ry;

        $this->_canvas->path()
            ->moveTo($x + $rx, $y)
            ->curveTo($x + $rx, $y - $ly, $x + $lx, $y - $ry, $x, $y - $ry)
            ->curveTo($x - $lx, $y - $ry, $x - $rx, $y - $ly, $x - $rx, $y)
            ->curveTo($x - $rx, $y + $ly, $x - $lx, $y + $ry, $x, $y + $ry)
            ->curveTo($x + $lx, $y + $ry, $x + $rx, $y + $ly, $x + $rx, $y);

        $this->_drawStyle($style);

        return $this;
    }

    /**
     * Draws a polygon on the canvas.
     *
     * @param float[] $points Array of the form (x1, y1, x2, y2, ..., xn, yn) where (x1, y1) is the starting point
     *                        and (xn, yn) is the last one. Must contain at least 3 points.
     * @param int $style
     * @return SetaPDF_Core_Canvas_Draw
     */
    public function polygon(array $points, $style = self::STYLE_DRAW)
    {
        $pointCount = count($points);
        if ($pointCount % 2 === 1) {
            throw new InvalidArgumentException('Uneven count of coordinates given for polygon.');
        }
        if ($pointCount < 6) {
            throw new InvalidArgumentException(sprintf(
                'A polygon requires at least 3 points. Got %d points',
                $pointCount / 2
            ));
        }

        $points = array_values($points);
        $path = $this->_canvas->path();
        $path->moveTo($points[0], $points[1]);

        for ($i = 2; $i < $pointCount; $i += 2) {
            $path->lineTo($points[$i], $points[$i + 1]);
        }

        $this->_drawStyle($style);

        return $this;
    }

    /**
     * Call the specific path function depending on the used style.
     *
     * @param int $style
     */
    protected function _drawStyle($style)
    {
        $path = $this->_canvas->path();
        switch ($style) {
            case self::STYLE_FILL:
                $path->fill();
                return;
            case self::STYLE_DRAW_AND_FILL:
                $path->fillAndStroke();
                return;
            case self::STYLE_DRAW:
            default:
                $path->stroke();
                return;
        }
    }
}