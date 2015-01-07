<?php

use MatthiasMullie\Geo;

class ClustererTest extends PHPUnit_Framework_TestCase
{
    public function dataProvider() {
        return array(
            array(
                'bounds' => new Geo\Bounds(
                    // approximation of bounding box around Belgium
                    new Geo\Coordinate(51.474654, 6.344604),
                    new Geo\Coordinate(49.482639, 2.471924)
                ),
                'coordinates' => array(
                    new Geo\Coordinate(50.824167, 3.263889), // Kortrijk railway station
                    new Geo\Coordinate(51.035278, 3.709722), // Gent-Sint-Pieters railway station
                    new Geo\Coordinate(50.881365, 4.715682), // Leuven railway station
                    new Geo\Coordinate(50.860526, 4.361787), // Brussels North railway station
                    new Geo\Coordinate(50.836712, 4.337521), // Brussels South railway station
                    new Geo\Coordinate(50.845466, 4.357113), // Brussels Central railway station
                    new Geo\Coordinate(51.216227, 4.421180), // Antwerpen Central railway station
                ),
            ),
        );
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testClustered(Geo\Bounds $bounds, $coordinates) {
        $clusterer = new Geo\Clusterer($bounds);
        $clusterer->setNumberOfClusters(12);

        // 1 location per cell is enough to form a cluster
        $clusterer->setMinClusterLocations(1);

        foreach ($coordinates as $coordinate) {
            $clusterer->addCoordinate($coordinate);
        }

        // all coordinates should be clustered
        // the 3 Brussels railway stations should form 1 cluster
        $clusters = $clusterer->getClusters();
        $this->assertEquals(count($coordinates) - 2, count($clusters));
        $this->assertEquals(3, $clusters[3]->total);
        $this->assertEquals(0, count($clusterer->getCoordinates()));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testPartiallyClustered(Geo\Bounds $bounds, $coordinates) {
        $clusterer = new Geo\Clusterer($bounds);
        $clusterer->setNumberOfClusters(12);

        // some coordinates should be clustered, as soon as there's > 1 per
        // matrix cell
        $clusterer->setMinClusterLocations(2);

        foreach ($coordinates as $coordinate) {
            $clusterer->addCoordinate($coordinate);
        }

        // the 3 Brussels railway stations should be clustered, rest should be
        // returned as single coordinates
        $clusters = $clusterer->getClusters();
        $this->assertEquals(1, count($clusters));
        $this->assertEquals(3, $clusters[0]->total);
        $this->assertEquals(count($coordinates) - 3, count($clusterer->getCoordinates()));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testUnclustered(Geo\Bounds $bounds, $coordinates) {
        $clusterer = new Geo\Clusterer($bounds);
        $clusterer->setNumberOfClusters(12);

        // make sure coordinates aren't clustered!
        $clusterer->setMinClusterLocations(99);

        foreach ($coordinates as $coordinate) {
            $clusterer->addCoordinate($coordinate);
        }

        // no clusters, just all coordinates
        $this->assertEquals(0, count($clusterer->getClusters()));
        $this->assertEquals(count($coordinates), count($clusterer->getCoordinates()));
    }
}
