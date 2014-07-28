<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:import href="elements/header.xsl"/>
  <xsl:template match="jw-header">
    <head prefix="og: http://ogp.me/ns#">
      <xsl:apply-templates select="header_required"/>
      <meta charset="utf-8"/>
      <meta name="description" content=""/>
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
      <meta name="google-site-verification" content="jrG7QMwIluWHDRaFad1G36OBcuF7TUgz_fqz2-onqKc"/>
      <!--[if lt IE 9]>
     <script src="http://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7/html5shiv.min.js"></script>
     <script src="http://cdnjs.cloudflare.com/ajax/libs/css3pie/1.0.0/PIE.min.js"></script>
     <script type="text/javascript" src="{$themePath}/js/ie_sux.js"></script>
<![endif]-->
      <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css"/>
      <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css"/>
      <link rel="stylesheet" href="//use.typekit.net/c/47d109/freight-sans-pro:i4:n3:n4:n5:n6:n7.TJC:N:2,TJ8:N:2,TJB:N:2,TJD:N:2,TJG:N:2,TJJ:N:2/d?3bb2a6e53c9684ffdc9a9bf01f5b2a62dc1020e61a7e41334f5590cfa9d950886e9d44ce579f893fbc5c512cf25da9af1c1dbc4fdcb233f6f8050029239c0599aa6ab7b6cb5962a391ac07cf5a57eff53c4963fb7c78a1f2c4c7a9b2b9d701b6adddcfc6ad97eacdd287c11f2e08f018bd743d77e7b3dfd6a90ffa7a9c4f0fd55979ffa3ce1d0aa3f68b7b750d6676b2cd4d3564924327e06044485e4e1fa118dbc6d748c6"/>
      <link rel="stylesheet" href="{$themePath}/css/screen.css"/>
      <script src="//cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.min.js" type="text/javascript"/>
    </head>
  </xsl:template>
  <xsl:template match="view">
    <jw-header/>
    <xsl:apply-templates select="jw-header"/>
    <body class="home {@logged-in}" data-pageViewName="HomePageView" data-backgroundImageUrl="{@background-url}">
      <div class="overlay o-connect">
        <div class="o-background">
      </div>
        <div class="o-content"><h1>Create a walk</h1><a href="{profile/@login}" class="btn btn-primary">Log in</a> or
        <a href="{profile/@register}" class="btn btn-primary">Join</a>
        to create a walk
      </div>
      </div>
      <div class="backgroundImageBanner faded"/>
      <div class="intro full">
        <div class="callouts">
          <blockquote class="homepage-callout2">
            <xsl:apply-templates select="area[@name='Intro']"/>
          </blockquote>
        </div>
      </div>
      <!-- end of .intro -->
      <div class="overlap" id="getinvolved">
        <xsl:if test="$isMobile">
          <ul class="controls">
            <li>
              <a class="showButton">Show Map <br/><i class="fa fa-chevron-down"/></a>
              <a class="closeButton" style="display:none">Close Map <br/><i class="fa fa-chevron-up"/></a>
            </li>
          </ul>
          <section class="map full">
            <xsl:apply-templates select="area[@name='Map']"/>
          </section>
        </xsl:if>
        <section class="calltoaction full">
          <xsl:apply-templates select="area[@name=string('Call to Action')]"/>
        </section>
      </div>
      <section class="blog full">
        <div>
          <section class="walkblog">
            <xsl:apply-templates select="area[@name=string('Blog Header')]"/>
            <xsl:apply-templates select="area[@name=Blog]"/>
          </section>
          <section class="twitter">
            <h3>Twitter</h3>
            <xsl:apply-templates select="area[@name=Twitter]"/>
          </section>
        </div>
      </section>
      <section class="sponsors full">
        <xsl:apply-templates select="area[@name=Sponsors]"/>
      </section>
    </body>
  </xsl:template>
  <xsl:template match="area">
    <xsl:apply-templates select="node()|@*"/>
  </xsl:template>
  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="node()|@*"/>
    </xsl:copy>
  </xsl:template>
</xsl:stylesheet>
