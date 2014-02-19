<?php 
  $nh = Loader::helper('navigation');
  $im = Loader::helper('image');
  $dh = Loader::helper('concrete/dashboard');
  $th = Loader::helper('theme');
  $av = Loader::helper('concrete/avatar');
  global $u; global $cp;
  $this->inc('elements/header.php');
?>
<body class="walk-page active-walk"
  data-pageViewName="WalkPageView">
  <div id="fb-root"></div>
  <script type="text/javascript">
    window.fbAsyncInit = function() {
      FB.init({
        appId: '544710848887303',
        status: true,
        xfbml: true
      });
    };
    (function(d, s, id){
       var js, fjs = d.getElementsByTagName(s)[0];
       if (d.getElementById(id)) {return;}
       js = d.createElement(s); js.id = id;
       js.src = '//connect.facebook.net/en_US/all.js';
       fjs.parentNode.insertBefore(js, fjs);
     }(document, 'script', 'facebook-jssdk'));
  </script>
  <script type="text/javascript">
    JanesWalk = {
      page: {
        url: 'http://' + (location.host) + (location.pathname),
        pictureUrl: 'http://i.imgur.com/JgaVx8G.png',
        title: '<?= addslashes($c->getCollectionName()) ?>',
        description: '<?= addslashes(strip_tags($c->getAttribute('longdescription'))) ?>',
        city: {
          name: 'Toronto',
          url: 'http://janeswalk.org/toronto'
        },
        gmap: <?=json_encode($gmap)?>
      }
    };
  </script>
  <?php $this->inc('elements/navbar.php'); ?>
  <div class="container-outter" role="main">
    <div class="container">

      <ul class="breadcrumb visible-desktop visible-tablet">
        <?php
        foreach($crumbs as $crumb) {
          if( $crumb->getCollectionID() == 1 ) { ?>
          <li><a href="<?=$nh->getLinkToCollection($crumb)?>"><i class="icon-home"></i></a> <span class="divider"><i class="icon-angle-right"></i></span></li>
          <?php } else if ($crumb->getCollectionTypeHandle() != 'country' ) { ?>
          <li><a href="<?=$nh->getLinkToCollection($crumb)?>"><?=$crumb->getCollectionName() ?></a><span class="divider"><i class="icon-angle-right"></i></span></li>
        <?php }
        } ?>
        <li class="active"><?=$c->getCollectionName() ?></li>
      </ul>

      <div class="walk-label">Festival Walk</div>

      <div class="tag-list">
        <ul class="nav nav-pills">
          <?php foreach((object)$c->getAttribute("theme") as $theme) { ?>
            <li><div class='icon'><?=$th->getIcon($theme)?></div> <?=$th->getName($theme)?></li>
          <?php } ?>
        </ul>
      </div>

      <div class="row-fluid walk-header">
        <div class="span9">
          <h1 class="walk-title"><?=$c->getCollectionName()?></h1>
        </div>

        <div class="span3 profiles box-sizing">
          <div id="reg-group">
            <?php
              $slots = (Array)$scheduled['slots']; 
              if($scheduled['open']) {
            ?>
              <h4 class="available-time"><i class="icon-calendar"></i> Open schedule</h4>
            <?php
              } else if(isset($slots[0]['date'])) {
            ?>
              <h4 class="available-time"><i class="icon-calendar"></i> Next available day:<br /><span class="highlight"><?=$slots[0]['date']?></span></h4>
            <?php }
              if ((string) $c->getAttribute('show_registration_button') === 'Yes') {
                if(!empty($eid)) {
            ?>
              <a data-eid="<?=$eid?>" href="<?="http://eventbrite.ca/event/$eid" ?>" id="register-btn" class="btn btn-primary btn-large">Register For This Walk</a>
            <?php
              } else {
            ?>
              Registration Not Yet Open
            <?php
              }
            }
            ?>
          </div>
        </div>
      </div>

      <div class="row-fluid walk-leaders clearfix">
        <div class="span7">
          <h4>
            <?='Walk Leader' . (sizeof($walk_leaders) === 1 ? ': ' : 's: ') .
              implode(', ', array_map(function($mem){ return "{$mem['name-first']} {$mem['name-last']}"; }, $walk_leaders)); ?>
          </h4>
        </div>
      </div>
      <?php if(sizeof((array)$gmap->markers) + sizeof((array)$gmap->path) > 0) { ?>
      <div class="hero-unit walk-stops" style="display:none">
        <div class="row-fluid">
          <div class="span12">
            <div class="walk-stops-meta box-sizing">
              <header id="header" class="walk-stops-meta-inner">
              <?php if (isset($slots[0]['duration'])) { ?>
              <h4><i class="icon-time"></i> Duration:</h4>
              <h5>
                Approximately <?=$slots[0]['duration'] ?>
              </h5>
              <?php } else { ?>
              <h4><i class="icon-time"></i> Open Schedule</h4>
              <?php } ?>
              <hr>
              <h4><i class="icon-map-marker"></i> Walk Route</h4>
              <h5 class="clickdetails">Click locations to see details</h5>
              <ol>
                <?php foreach($gmap->markers as $key=>$marker) { ?>
                  <li class='walk-stop' id='<?=$key?>'><h4><?=$marker->title?></h4></li>
                <?php } ?>
              </ol>
              </header>
            </div>
            <div id="map-canvas-wrapper">
              <div id="map-canvas">
                <div class="infobox-wrapper">
                  <div id="infobox">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>

      <div class="row-fluid walk-body">
        <div class="span8">
          <div class="clearfix">
            <h3>About This Walk</h3>
            <?php if( $thumb = $c->getAttribute("thumbnail") ) { ?>
              <a class='thumb' href='<?=$im->getThumbnail($thumb,1024,1024)->src?>'><img src='<?=$im->getThumbnail($thumb,340,720)->src?>' class='pull-right img-polaroid' /></a>
            <?php } 
            echo $c->getAttribute('longdescription'); ?>
          </div>

          <div class="clearfix walk-team">
            <hr>
            <h3 id="walk-leader-bio">About The Walk Team</h3>

            <?php foreach($team as $mem) { ?>
            <div class="walk-leader clearfix"> 
              <div class="row-fluid">
                <div class="span3">
                  <?php if($mem['avatar']) { ?>
                    <div class='u-avatar' style='background-image:url(<?=$mem['avatar']?>)' class='pull-left'></div>
                  <? } else { ?>
                    <img src='<?=$mem['image']?>' alt='<?=$mem['title']?>' class='pull-left'>
                  <? } ?>
                </div>
                <div class='span9'>
                  <h4>
                    <span class='title'><?=$mem['title']?></span><br /><?="{$mem['name-first']} {$mem['name-last']}"?>
                  </h4>
                  <div class="btn-toolbar">
                    <?php if($mem['email']) { ?><a href="mailto:<?=$mem['email']?>" class="btn"><i class="icon-envelope-alt"></i></a><?php } ?>
                    <?php if($mem['facebook']) { ?><a href="http://facebook.com/<?=$mem['facebook']?>"><i class="icon-facebook"></i></a><?php } ?>
                    <?php if($mem['twitter']) { ?><a href="http://twitter.com/<?=$mem['twitter']?>"><i class="icon-twitter"></i></a><?php } ?>
                  </div>
                  <?=$mem['bio']?>
                </div>
              </div>
            </div>
            <?php } ?>
          </div><!-- About The Walk Leader Section -->

          <hr>

          <h3>Downloads</h3>

          <div class="download-list">
            <ul>
            </ul>
          </div> <!-- Download -->

          <hr>

          <div class="share-print">
            <a href="#" class="share notify printLink"><i class="icon-print"></i> Print this walk</a>
            <a href="#" class="share notify facebookShareLink"><i class="icon-share"></i> Share this walk</a>
          </div>

        </div>

        <aside class="span4">
        <div class="thumbnail" id="register">
          <?php
            if ((string) $c->getAttribute('show_registration_button') === 'Yes') {
              if(!empty($eid)) {
          ?>
            <div class="caption">
              <h3>
                <i class="icon-calendar"></i> 
                <a href="<?="http://eventbrite.ca/event/$eid"?>" >Register For This Walk</a>
                <p class="select-day"></p>
              </h3>
            </div>
          <?php
              } else {
          ?>
            <div class="caption">
              <h3>
                <i class="icon-calendar"></i> 
                Registration Not Open
                <p class="select-day"></p>
              </h3>
            </div>
          <?php
              }
            }
          ?>
          <div class="calendar-wrap box-sizing" style="display:none">

            <div class="calendar-header">
              <button id="custom-prev" class="custom-month btn btn-mini btn-primary pull-left"><i class="icon-caret-left"></i></button>      
              <span id="custom-month">April</span>, <span id="custom-year">2013</span> 
              <button id="custom-next" class="custom-month btn btn-mini btn-primary pull-right"><i class="icon-caret-right"></i></button>
            </div>

            <div id="calendar" class="fc-calendar-container"></div>
          </div>
          <div class="caption" style="display:none">
            <div class="date-caption">
              <div class="request-nowalks"><small>There is no scheduled walk for this day</small></div>
              <a href="#" class="btn btn-primary request-btn">Request this day</a>
            </div>
          </div>
          <div class="request" style="display:none">
            <div class="row-fluid">
              <div class="span">
                <label for="date-picker">Date</label>
                <input type="text" value="12-02-2012" id="date-picker">
              </div>
            </div> 
            <div class="row-fluid">
              <div class="span6">
                <label for="request-number">Number of people</label>
                <input type="text" id="request-number" />
              </div>
            </div>
            <label for="request-body">Add a Note:</label>
            <textarea name="" id="request-body" cols="30" rows="5"></textarea>
            <label for="request-email">Your email</label> 
            <input type="text" id="request-email" />
            <button class="btn btn-primary notify">
              Request Date
            </button>
          </div>
          <div class="alert alert-info"><p>While Walks are free and volunteer led, support for Jane's Walk can be recognized through a donation as part of a Walk booking.</p></div>
        </div>

        <div class="thumbnail accessibility">
          <div class="caption">
            <h4><i class="icon-accessible"></i> Accessibility</h4>
            <ul>
              <?php foreach((object)$c->getAttribute("accessible") as $accessible) { ?><li><?=$th->getName($accessible)?></li><?php } ?>
            </ul>
            <?php if($accessible_info = trim($c->getAttribute('accessible_info'))) {?>
            <p id="accessibility notes">
              <?=$accessible_info?>
            </p>
            <?php }
            if($public_transit = trim($c->getAttribute('accessible_transit'))) { ?>
            <h4><i class="icon-transit"></i> Taking Public Transit</h4>
            <p id="public transit directions">
              <?=$public_transit ?>
            </p>
            <?php }
            if($accessible_parking = trim($c->getAttribute("accessible_parking"))) {
            ?>
            <h4><i class="icon-road"></i> Parking Availability</h4>
            <p id="parking availability">
              <?=$accessible_parking ?>
            </p>
            <?php }
            if($accessible_find = trim($c->getAttribute("accessible_find"))) { ?>
            <h4><i class="icon-flag"></i> How to find us</h4>
            <p>
              <?=$accessible_find ?>
            </p>
            <? } ?>
          </div>
        </div><!-- accessibility -->

        </aside>
      </div>
      <hr>
      <h3><i class="icon-comments-alt"></i> Feedback</h3>
      <div class="row-fluid">
        <div class="span8">
          <div class="well">
            <div id="disqus_thread"></div>
            <script type="text/javascript">
              /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
              var disqus_shortname = 'janeswalk'; // required: replace example with your forum shortname

              /* * * DON'T EDIT BELOW THIS LINE * * */
              (function() {
                var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
                dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
                (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
              })();
            </script>
            <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
            <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>

          </div>
        </div>  
      </div>
    </div>
  </div> 
  <script type="text/javascript">
    // EventBrite
    var EventBriteEmail = 'jasmine.frolick@janeswalk.net';
    $('a.thumb').colorbox({
      rel: 'group1',
      onOpen: blurPage,
      onCleanup: unblurPage,
      current: 'Picture {current} of {total}',
      previous: '&lt;',
      next: '&gt;',
      close: 'x',
      maxHeight: '80%',
      maxWidth: '80%'
    });
  </script>

  <?php $this->inc('elements/footer.php');?>
