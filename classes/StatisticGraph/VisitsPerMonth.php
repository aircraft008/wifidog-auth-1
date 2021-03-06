<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +-------------------------------------------------------------------+
// | WiFiDog Authentication Server                                     |
// | =============================                                     |
// |                                                                   |
// | The WiFiDog Authentication Server is part of the WiFiDog captive  |
// | portal suite.                                                     |
// +-------------------------------------------------------------------+
// | PHP version 5 required.                                           |
// +-------------------------------------------------------------------+
// | Homepage:     http://www.wifidog.org/                             |
// | Source Forge: http://sourceforge.net/projects/wifidog/            |
// +-------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or     |
// | modify it under the terms of the GNU General Public License as    |
// | published by the Free Software Foundation; either version 2 of    |
// | the License, or (at your option) any later version.               |
// |                                                                   |
// | This program is distributed in the hope that it will be useful,   |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of    |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the     |
// | GNU General Public License for more details.                      |
// |                                                                   |
// | You should have received a copy of the GNU General Public License |
// | along with this program; if not, contact:                         |
// |                                                                   |
// | Free Software Foundation           Voice:  +1-617-542-5942        |
// | 59 Temple Place - Suite 330        Fax:    +1-617-542-2652        |
// | Boston, MA  02111-1307,  USA       gnu@gnu.org                    |
// |                                                                   |
// +-------------------------------------------------------------------+

/**
 * @package    WiFiDogAuthServer
 * @subpackage Statistics
 * @author     Philippe April
 * @copyright  2005-2006 Philippe April
 * @version    Subversion $Id: VisitsPerMonth.php 1393 2009-06-25 03:15:37Z benoitg $
 * @link       http://www.wifidog.org/
 */

/**
 * Load required classes
 */
require_once('classes/StatisticGraph.php');

/**
 * @package    WiFiDogAuthServer
 * @subpackage Statistics
 * @author     Philippe April
 * @copyright  2005-2006 Philippe April
 */
class VisitsPerMonth extends StatisticGraph
{
    /** Get the Graph's name.  Must be overriden by the report class
     * @return a localised string */
    public static function getGraphName()
    {
        return _("Number of individual user visits per month");
    }

    /** Constructor, must be called by subclasses */
    protected function __construct()
    {
        parent :: __construct();
    }

    /** Get the actual report.
     * Classes can (but don't have to) override this, but must call the parent's
     * method with what would otherwise be their return value and return that
     * instead.
     * @param $statistics_object Mandatory to give the report it's context
     * @param $child_html The child method's return value
     * @return A html fragment
     */
    public function getReportUI(Statistics $statistics_object, $child_html = null)
    {
        $html = '';
        $html .= _("Note:  A visit is like counting connections, but only counting one connection per day for each user at a single node");
        return parent::getReportUI($statistics_object, $html);
    }

    /** Return the actual Image data
     * Classes must override this.
     * @param $child_html The child method's return value
     * @param $param mixed: used for $Graph->done()
     * @return A html fragment
     */
    public function showImageData($child_html='', $param=false)
    {
        require_once ("Image/Graph.php");
        $db = AbstractDb::getObject();
        $Graph =& Image_Graph::factory("Image_Graph", array(600, 200));
        $Plotarea =& $Graph->add(Image_Graph::factory("Image_Graph_Plotarea"));
        $Dataset =& Image_Graph::factory("Image_Graph_Dataset_Trivial");
        $Bar =& Image_Graph::factory("Image_Graph_Plot_Bar", $Dataset);
        $Bar->setFillColor("#9db8d2");
        $Plot =& $Plotarea->add($Bar);

                $candidate_connections_sql = self :: $stats->getSqlCandidateConnectionsQuery("COUNT(DISTINCT connections.user_id||connections.node_id) AS daily_connections, date_trunc('day', timestamp_in) AS date");
        $db->execSql("SELECT SUM(daily_connections) AS connections, date_trunc('month', date) AS month FROM ($candidate_connections_sql GROUP BY date) AS daily_connections_table GROUP BY month ORDER BY month", $results, false);
        if ($results != null) {
            foreach($results as $row) {
                /* Cut xxxx-xx-xx xx:xx:Xx to yy-mm */
                $Dataset->addPoint( substr($row['month'],0,7), $row['connections']);
            }
        }

        $Graph->done($param);
        unset( $Graph, $Plot, $Bar, $Plotarea, $Dataset, $row, $results );
    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */


