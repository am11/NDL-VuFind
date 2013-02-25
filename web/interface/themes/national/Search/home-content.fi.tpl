<!-- START of: Search/home-content.fi.tpl -->

<div id="introduction" class="section clearfix">
  <div class="container_24">
    <div class="grid_14">
      <div id="siteDescription">
        <h2>Tietoa tarvitseville ja elämyksiä etsiville</h2>
        <p class="big">Finna on uudenlainen tiedonhakupalvelu kaikille arkistojen, kirjastojen ja museoiden palveluiden käyttäjille.</p>
        <p class="big">Finna on nyt testikäytössä. Kokeile hakua, <a href="{$path}/Feedback/Home">anna palautetta</a> tai <a class="color-violet" href="{$path}/Content/about">lue lisää</a> palvelusta!</p>
      </div>
    </div>
    <div class="grid_10 push_right">
      <div>
        <h2>Haulla löydät...</h2>
        <ul class="first grid_4 suffix_1">
          <li><span class="iconlabel formatdocument"><a href='{$url}/Search/Results?filter[]=format%3A"0%2FDocument"'>Asiakirjoja</a></span></li>
          <li><span class="iconlabel formatphysicalobject"><a href='{$url}/Search/Results?filter[]=format%3A"0%2FPhysicalObject"'>Esineitä</a></span></li>
          <li><span class="iconlabel formatmap"><a href='{$url}/Search/Results?filter[]=format%3A"0%2FMap"'>Karttoja</a></span></li>
          <li><span class="iconlabel formatbook"><a href='{$url}/Search/Results?filter[]=format%3A"0%2FBook"'>Kirjoja</a></span></li>
          <li><span class="iconlabel formatimage"><a href='{$url}/Search/Results?filter[]=format%3A"0%2FImage"'>Kuvia</a></span></li>
          <li><span class="iconlabel formatjournal"><a href='{$url}/Search/Results?filter[]=format%3A"0%2FJournal"'>Lehtiä&nbsp;ja&nbsp;artikkeleita</a></span></li>
        </ul>
        <ul class="grid_3">
          <li><span class="iconlabel formatmusicalscore"><a href='{$url}/Search/Results?filter[]=format%3A"0%2FMusicalScore"'>Nuotteja</a></span></li>
          <li><span class="iconlabel formatthesis"><a href='{$url}/Search/Results?filter[]=format%3A"0%2FThesis"'>Opinnäytteitä</a></span></li>
          <li><span class="iconlabel formatworkofart"><a href='{$url}/Search/Results?filter[]=format%3A"0%2FWorkOfArt"'>Taideteoksia</a></span></li>
          <li><span class="iconlabel formatdatabase"><a href='{$url}/Search/Results?filter[]=format%3A"0%2FDatabase"'>Tietokantoja</a></span></li>
          <li><span class="iconlabel formatvideo"><a href='{$url}/Search/Results?filter[]=format%3A"0%2FVideo"'>Videoita</a></span></li>
          <li><span class="iconlabel formatsound"><a href='{$url}/Search/Results?filter[]=format%3A"0%2FSound"'>Äänitteitä</a></span></li>
        </ul>
      </div>
    </div>
  </div>
</div>
<div id="content-carousel" class="section clearfix">
  <div class="container_24">
    <div id="carousel">
      {include file="Search/home-carousel.$userLang.tpl"}
    </div>
  </div>
</div>
<div id="popular-map" class="section clearfix">
  <div class="container_24">
    <div class="grid_14">
      <div id="topSearches">
        <h2>10 suosituinta hakua</h2>
        <div id="popularSearches" class="recent-searches"><div class="loading"></div></div>
        {include file="AJAX/loadPopularSearches.tpl"}
      </div>
    </div>
    <div class="grid_10 push_right">
      <div id="mapSearchHome">
        <h2>Kokeile karttahakua</h2>
        <p>Voit rajata hakuasi myös kartalla. Karttarajauksen piirissä on tällä hetkellä noin 12630 aineistotietoa.</p>
        <a class="button" href="{$url}/Search/Advanced">Karttahakuun</a>
      </div>
    </div>
  </div>
</div>
    
<!-- END of: Search/home-content.fi.tpl -->
