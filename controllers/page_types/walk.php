<?php
use \JanesWalk\Models\PageTypes\Walk;
use \JanesWalk\Controllers\Controller;
use \JanesWalk\Libraries\MirrorWalk\MirrorWalk;

defined('C5_EXECUTE') or die("Access Denied.");
Loader::library('Eventbrite');
Loader::controller('/janes_walk');
Loader::model('page_types/Walk');
Loader::library('MirrorWalk/MirrorWalk');

class WalkPageTypeController extends Controller
{
    /**
     * @type Walk $walk The Walk model
     */
    protected $walk;

    public function on_start()
    {
        $this->walk = new Walk($this->c);

        /* Ideally this should be in a router, not the individual on_start.
         * c5.7 uses symfony2 for routing
         * TODO: either use the c5.6 'Request' class, or wait for 5.7.
         */
        switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $this->create($_POST['json']);
            break;
        case 'PUT':
            parse_str(file_get_contents('php://input'),$put_vars);
            $this->update($put_vars['json']);
            break;
        case 'GET':
            $this->show();
            break;
        case 'DELETE':
            $this->destroy();
            break;
        }
    }

    /**
     * show
     * Render view contents. Fall-through behaviour renders theme as HTML via view(). If 'format' is set, render in requested format
     */
    public function show()
    {
        if ($_GET['format'] === 'json') {
            // Render JSON
            header('Content-Type: application/json');
            echo $this->getJson();
            exit;
        } elseif ($_GET['format'] === 'kml' || 0 === strpos($_SERVER['HTTP_USER_AGENT'],'Kml-Google')) {
            // Render KML of map only
            header('Content-Type: application/vnd.google-earth.kml+xml');
            $this->getKml()->save('php://output');
            exit;
        }
    }

    /**
     * create
     * Saves a version of Walk collection, and makes it live
     *
     * @param string $json String-encoded JSON of walk
     */
    public function create($json)
    {
        header('Content-Type: application/json');
        try {
            // Save the walk
            $cvID = $this->setJson($json, true);
             // Send requests to all walk-mirroring services
            $mw = new MirrorWalk($this->walk);
            $mw->mirrorStart();

            echo json_encode([
                'cID' => $this->walk->getPage()->getCollectionID(),
                'cvID' => $cvID
            ]);

            // Wait until walk-mirrroring blocking code completes
            $mw->mirrorEnd();
        } catch (Exception $e) {
            Log::addEntry('Walk error on walk ' . __FUNCTION__ . ': ', $e->getMessage());
            echo json_encode([
                'cID' => $this->walk->getPage()->getCollectionID(),
                'error' => true,
                'action' => __FUNCTION__,
                'msg' => (string) $e->getMessage()
            ]);
            http_response_code(500);
        } finally {
            exit;
        }
    }

    /**
     * update
     * Saves a version of the walk collection, but doesn't approve version
     *
     * @param string $json String-encoded JSON of walk
     */
    public function update($json)
    {
        header('Content-Type: application/json');
        try {
            $cvID = $this->setJson($json);
    
            // Set the eventbrite
            $mw = new MirrorWalk($this->walk);
            $mw->mirrorStart();

            echo json_encode([
                'cID' => $this->walk->getPage()->getCollectionID(),
                'cvID' => $cvID
            ]);

            $mw->mirrorEnd();
        } catch (Exception $e) {
            Log::addEntry('Walk error on walk ' . __FUNCTION__ . ': ', $e->getMessage());
            echo json_encode([
                'cID' => $this->walk->getPage()->getCollectionID(),
                'error' => true,
                'action' => __FUNCTION__,
                'msg' => (string) $e->getMessage()
            ]);
            http_response_code(500);
        } finally {
            exit;
        }
    }

    /*
     * destroy
     * Simply unpublishes the walk
     */
    public function destroy()
    {
        header('Content-Type: application/json');
        $this->c->setAttribute('exclude_page_list',true);
        $this->setEventBriteStatus('draft');
        echo json_encode([
            'cID' => $this->walk->getPage()->getCollectionID(),
        ]);
        exit;
    }

    /**
     * getJson
     *
     * @return string of walk's json
     */
    protected function getJson()
    {
        return json_encode($this->walk);
    }

    /**
     * setJson
     * Creates a new walk page version based on a json string
     *
     * @param $json String
     * @param $public bool
     * @return int cvID of new collection version
     */
    protected function setJson($json, $publish = false)
    {
        $currentCollectionVersion = $this->walk->getPage()->getVersionObject();
        $newCollectionVersion = $currentCollectionVersion->createNew('Updated via walk form');
        $this->walk->getPage()->loadVersionObject($newCollectionVersion->getVersionID());

        /* Set the model by the json envelope */
        $this->walk->setJson($json);

        /* We use 'exclude_page_list' to 'unpublish' walks */
        if ($publish) {
            $c->setAttribute('exclude_page_list',false);
            $newCollectionVersion->approve();
        }

        return (int) $newCollectionVersion->getVersionID();
    }

    /**
     * getKml()
     *
     * @return DOMDocument of KML map for walk
     */
    protected function getKml()
    {
        return $this->walk->kmlSerialize();
    }

    public function view()
    {
        parent::view();
        $nh = Loader::helper('navigation');
        $im = Loader::helper('image');
        $c = $this->getCollectionObject();

        // Put the preview image for Facebook/Twitter to pick up
        $doc = new DOMDocument;
        if ($thumb) {
            $meta = $doc->appendChild($doc->createElement('meta'));
            $meta->setAttribute('property','og:image');
            $meta->setAttribute('content', BASE_URL . $im->getThumbnail($thumb,340,720)->src);
        }
        $meta = $doc->appendChild($doc->createElement('meta'));
        $meta->setAttribute('property','og:url');
        $meta->setAttribute('content', $nh->getCollectionURL($c));
        $meta = $doc->appendChild($doc->createElement('meta'));
        $meta->setAttribute('property','og:title');
        $meta->setAttribute('content', $c->getCollectionName());
        $meta = $doc->appendChild($doc->createElement('meta'));
        $meta->setAttribute('property','og:description');
        $meta->setAttribute('content', $c->getAttribute('shortdescription'));
        $this->addHeaderItem($doc->saveHTML());

        // Breadcrumb trail to walk
        $bc = $doc->appendChild($doc->createElement('ul'));
        $bc->setAttribute('class', 'breadcrumb visible-desktop visible-tablet');
        foreach ((array) $this->walk->crumbs as $crumb) {
            if ($crumb->getCollectionTypeHandle() !== 'country' ) {
                $li = $bc->appendChild($doc->createElement('li'));
                $a = $li->appendChild($doc->createElement('a'));
                $a->setAttribute('href', $nh->getLinkToCollection($crumb));
                if ($crumb->getCollectionID() === '1') {
                    $a->appendChild($doc->createElement('i'))->setAttribute('class','fa fa-home');
                } else {
                    $linkText = $crumb->getCollectionName();
                    if ($crumb->getCollectionTypeHandle() === 'city') {
                        $linkText .= ' walks';
                    }
                    $a->appendChild($doc->createTextNode(t($linkText)));
                }
                if ($k !== count($this->walk->crumbs)) {
                    $span = $li->appendChild($doc->createElement('span'));
                    $span->setAttribute('class','divider');
                    $span->appendChild($doc->createElement('i'))->setAttribute('class','fa fa-angle-right');
                }
            }
        }
        $li = $bc->appendChild($doc->createElement('li'));
        $li->setAttribute('class','active');
        $li->appendChild($doc->createTextNode($c->getCollectionName()));
        $this->set('breadcrumb', $doc->saveHTML($bc));

        /* Helpers to use in the view */
        $this->set('im', $im);
        $this->set('th', Loader::helper('theme'));
        $this->set('eid', $c->getAttribute('eventbrite'));
        $this->set('w', $this->walk);

        // Setup the page data needed in the script block
        $this->addToJanesWalk([
            'page' => [
                'description' => strip_tags($c->getAttribute('longdescription')),
                'city' => [
                    'name' => (string) $this->walk->city,
                    'url' => $nh->getCollectionURL($this->walk->city->getPage()),
                ],
                'gmap' => $this->walk->map,
            ]
        ]);
    }

    /**
     * Controller only action to transfer a walk to a different uid
     *
     * TODO: on 5.7, we have good routing with symfony. Get rid of these c5
     * convention endpoints, e.g. 'transfer', and use all restful routes as above
     */
    public function transfer($uid)
    {
        $c = $this->getCollectionObject();
        $p = new Permissions($c);
        if ($p->canWrite()) {
            $c->update(['uID' => $uid]);
            echo json_encode([
                'error' => null,
                'cID' => $c->getCollectionID(),
                'uID' => $uid
            ]);
        } else {
            echo json_encode([
                'error' => 'Cannot transfer walk: insufficient permissions'
            ]);
        }
        // Lazy way to make this a service endpoint
        exit;
    }
}
