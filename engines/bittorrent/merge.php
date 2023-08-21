<?php
    class TorrentSearch extends EngineRequest {
        public function __construct($query, $page, $mh, $config) {
            $this->query = $query;
            $this->page = $page;

            // TODO make these engine requests
            require "engines/bittorrent/thepiratebay.php";
            require "engines/bittorrent/rutor.php";
            require "engines/bittorrent/nyaa.php";
            require "engines/bittorrent/yts.php";
            require "engines/bittorrent/torrentgalaxy.php";
            require "engines/bittorrent/1337x.php";
            require "engines/bittorrent/sukebei.php";

            $query = urlencode($query);

            $torrent_urls = array(
                $thepiratebay_url,
                $rutor_url,
                $nyaa_url,
                $yts_url,
                $torrentgalaxy_url,
                $_1337x_url,
                $sukebei_url
            );

            $this->chs = array();

            foreach ($torrent_urls as $url)
            {
                $ch = curl_init($url);
                curl_setopt_array($ch, $config->curl_settings);
                array_push($this->chs, $ch);
                curl_multi_add_handle($mh, $ch);
            }
        }

        public function get_results() {
            $query = urlencode($this->query);
            $results = array();
            for ($i=0; count($this->chs)>$i; $i++)
            {
                $response = curl_multi_getcontent($this->chs[$i]);

                switch ($i)
                {
                    case 0:
                        $results = array_merge($results, get_thepiratebay_results($response));
                        break;
                    case 1:
                        $results = array_merge($results, get_rutor_results($response));
                        break;
                    case 2:
                        $results = array_merge($results, get_nyaa_results($response));
                        break;
                    case 3:
                        $results = array_merge($results, get_yts_results($response));
                        break;
                    case 4:
                        //$results = array_merge($results, get_torrentgalaxy_results($response));
                        break;
                    case 5:
                        //$results = array_merge($results, get_1337x_results($response));
                        break;
                    case 6:
                        $results = array_merge($results, get_sukebei_results($response));
                        break;
                }
            }

            $seeders = array_column($results, "seeders");
            array_multisort($seeders, SORT_DESC, $results);

            return $results; 
        }

        public static function print_results($results) {
            echo "<div class=\"text-result-container\">";

            if (!empty($results)) 
            {
                foreach($results as $result)
                {
                    $source = $result["source"];
                    $name = $result["name"];
                    $magnet = $result["magnet"];
                    $seeders = $result["seeders"];
                    $leechers = $result["leechers"];
                    $size = $result["size"];

                    echo "<div class=\"text-result-wrapper\">";
                    echo "<a href=\"$magnet\">";
                    echo "$source";
                    echo "<h2>$name</h2>";
                    echo "</a>";
                    echo "<span>SE: <span class=\"seeders\">$seeders</span> - ";
                    echo "LE: <span class=\"leechers\">$leechers</span> - ";
                    echo "$size</span>";
                    echo "</div>";
                }
            }
            else
                echo "<p>There are no results. Please try different keywords!</p>";

            echo "</div>";
        }
    }

?>
