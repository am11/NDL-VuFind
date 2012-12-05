<!-- START of: Content/searchhelp.fi.tpl -->

<h1 style="margin-left: 1em;">Hakuohje</h1>
<ul class="HelpMenu">
  <li><a href="#Wildcard Searches">Jokerimerkit</a></li>
  <li><a href="#Fuzzy Searches">Sumeat haut</a></li>
  <li><a href="#Proximity Searches">Etäisyyshaut</a></li>
  <li><a href="#Range Searches">Arvovälihaut</a></li>
  <li><a href="#Boosting a Term">Termin painottaminen</a></li>
  <li><a href="#Boolean operators">Boolean-hakuoperaattorit</a></li>
  <li><h5>Tarkennettu haku</h5></li>
  <li><a href="#Search Fields">Hakukentät</a></li>
  <li><a href="#Search Groups">Hakuryhmät</a></li>
</ul>

<div class="mainContent">

<dl class="Content">
  <dt><a name="Wildcard Searches"></a>Jokerimerkit</dt>
  <dd>
    <p><strong class="helpTerm">?</strong> korvaa yhden merkin hakutermistä.</p>
    <p>Esimerkki: termejä "text" ja "test" voidaan hakea samalla kyselyllä:</p>
    <pre class="code">te?t</pre>
    <p><strong class="helpTerm">*</strong> korvaa 0, 1 tai useampia merkkejä hakutermistä.</p>
    <p>Esimerkki: termejä "test", "tests" ja "tester" voidaan hakea kyselyllä:</p>
    <pre class="code">test*</pre>
    <p>Jokerimerkkejä voi käyttää myös hakutermin keskellä:</p>
    <pre class="code">te*t</pre>
    <p>Huomio! Jokerimerkkejä <strong>?</strong> ja <strong>*</strong> ei voi käyttää 
       hakutermin ensimmäisenä merkkinä.</p>
  </dd>
  
  <dt><a name="Fuzzy Searches"></a>Sumeat haut</dt>
  <dd>
    <p><strong class="helpTerm">~</strong> toteuttaa sumean haun yksisanaisen haun viimeisenä merkkinä.</p>
    <p>Esimerkki: sumea haku termille "roam":</p>
    <pre class="code">roam~</pre>
    <p>Tämä haku löytää esimerkiksi termit "foam" ja "roams".</p>
    <p>Haun samankaltaisuutta kantatermiin voidaan säädellä parametrilla, jonka arvo on 
       välillä 0 ja 1. Mitä lähempänä annettu arvo on lukua 1, sen samankaltaisempi 
       termin on oltava kantatermin kanssa.</p>
    <pre class="code">roam~0.8</pre>
    <p>Oletusarvona parametrille on 0.5, jos arvoa ei sumeassa haussa erikseen määritetä.</p>
  </dd>
  
  <dt><a name="Proximity Searches"></a>Etäisyyshaut</dt>
  <dd>
    <p><strong class="helpTerm">~</strong> toteuttaa etäisyyshaun monitermisen hakulausekkeen lopussa 
       etäisyysarvon kanssa.</p>
    <p>Esimerkki: etsitään termejä "economics" ja "keynes" niiden esiintyessä korkeintaan 10 termin etäisyydellä toisistaan:</p>   
    <pre class="code">"economics Keynes"~10</pre>
  </dd>
  
  {literal}
  <dt><a name="Range Searches"></a>Arvovälihaut</dt>
  <dd>
    <p>Arvovälihaut tehdään käyttämällä joko aaltosulkeita <strong>{ }</strong> tai 
       hakasulkeita <strong>[ ]</strong>. Aaltosulkeita käytettäessä huomioidaan vain 
       arvot annettujen termien välillä pois lukien kyseiset termit. Hakasulkeet 
       puolestaan sisällyttävät myös annetut termit etsittävälle arvovälille.
    <p>Esimerkki: etsittäessä termiä, joka alkaa kirjaimella B tai C, voidaan käyttää kyselyä:</p>
    <pre class="code">{A TO D}</pre>
    <p>Esimerkki: etsittäessä arvoja 2002&mdash;2003 voidaan haku tehdä seuraavasti:</p>
    <pre class="code">[2002 TO 2003]</pre>
    <p>Huomio! Sana TO arvojen välillä kirjoitetaan ISOIN KIRJAIMIN.</p>
  </dd>
  {/literal}
  
  <dt><a name="Boosting a Term"></a>Termin painottaminen</dt>
  <dd>
    <p><strong class="helpTerm">^</strong> nostaa termin painoarvoa kyselyssä.</p>
    <p>Esimerkki: haussa termin "Keynes" painoarvoa on nostettu:</p>
    <pre class="code">economics Keynes^5</pre>
  </dd>
  
  <dt><a name="Boolean operators"></a>Boolean-hakuoperaattorit</dt>
  <dd>
    <p>Termejä voi yhdistellä monimutkaisemmiksi kyselyiksi Boolean-hakuoperaattoreilla. 
       Seuraavat operaattorit ovat käytettävissä: <strong>AND</strong>, 
       <strong>+</strong>, <strong>OR</strong>, <strong>NOT</strong> ja <strong>-</strong>.
    </p>
    <p>Huomio! Boolean-hakuoperaattorit kirjoitetaan ISOIN KIRJAIMIN.</p>
    <dl>
      <dt><a name="AND"></a>AND</dt>
      <dd>
        <p><strong>AND</strong> eli konjunktio-operaattori on järjestelmän oletusarvoinen 
           operaattori monitermisille kyselyille, joihin ei ole sisällytetty mitään 
           operaattoria. <strong>AND</strong>-operaattoria käytettäessä kyselyn tuloksena saadaan tietueet, 
           joissa esiintyy kukin hakukentissä esiintyvistä termeistä.</p>
        <p>Esimerkki: etsitään tietueita, joissa esiintyy sekä "economics" että "Keynes":</p>
        <pre class="code">economics Keynes</pre>
        <p>tai</p>
        <pre class="code">economics AND Keynes</pre>
      </dd>
      <dt><a name="+"></a>+</dt>
      <dd>
        <p>Merkillä <strong>+</strong> voidaan ilmaista vaatimusta siitä, että termin on esiinnyttävä jokaisessa hakutuloksessa.</p>
        <p>Esimerkki: etsitään tietueita, joissa esiintyy ehdottomasti "economics" ja joissa voi lisäksi esiintyä "Keynes":</p>
        <pre class="code">+economics Keynes</pre>
      </dd>
      <dt><a name="OR"></a>OR</dt>
      <dd>
        <p><strong>OR</strong>-operaattorin käyttö haussa tuottaa tulokseksi tietueita, joissa 
           esiintyy yksi tai useampi operaattorin yhdistämistä termeistä.</p>
        <p>Esimerkki: etsitään tietueita, joissa esiintyy joko "economics Keynes" tai ainoastaan "Keynes":</p>
        <pre class="code">"economics Keynes" OR Keynes</pre>
      </dd>
      <dt><a name="NOT"></a>NOT / -</dt>
      <dd>
        <p><strong>NOT</strong>-operaattori poistaa hakutuloksista tietueet, joissa esiintyy kyselyssä 
           <strong>NOT</strong>-operaattoria seuraava termi.</p>
        <p>Esimerkki: etsitään tietueita, joissa on termi "economics" mutta ei termiä "Keynes":</p>
        <pre class="code">economics NOT Keynes</pre>
        <p>Huomio! NOT-operaattoria ei voi käyttää yksitermisissä kyselyissä.</p>
        <p>Esimerkki: seuraava kysely ei tuota lainkaan tuloksia:</p>
        <pre class="code">NOT economics</pre>
        <p><strong>NOT</strong>-operaattorin voi korvata operaattorilla <strong>-</strong>. </p>
      </dd>
    </dl>
  </dd>
</dl>

<h3>Tarkennettu haku</h3>

<dl class="Content">
  <dt><a name="Search Fields"></a>Hakukentät</dt>
  <dd>
    <p>Tarkennetun haun sivulla on useita hakukenttiä, joihin voi kirjoittaa 
       hakutermejä ja –lausekkeita sekä hakuoperaattoreita.</p>
    <p>Jokaisen hakukentän vieressä on alasvetovalikko, josta voi valita, mihin 
       tietueen kenttään haku kohdistetaan (otsikko, tekijä, ym.). Saman useita 
       termejä yhdistelevän haun voi tarvittaessa kohdistaa useampaan kenttään.</p>
    <p>Lisävalikko <strong>Hae</strong> määrittelee, miten useita hakukenttiä 
       sisältävä kysely käsitellään:</p>
    <ul>
      <li><strong>Kaikilla termeillä (AND)</strong> &mdash; Tuottaa tulokseksi tietueet, jotka täsmäävät 
          kaikkien hakukenttien sisältöön.</li>
      <li><strong>Millä tahansa termillä (OR)</strong> &mdash; Tuottaa tulokseksi tietueet, jotka 
          täsmäävät yhden tai useamman hakukentän sisältöön.</li>
      <li><strong>Ei millään termeistä (NOT)</strong> &mdash; Tuottaa tulokseksi tietueet, joissa ei 
          esiinny yhdenkään hakukentän sisältöä.</li>
    </ul>
    <p><strong>Lisää hakukenttä</strong> –painikkeella lomakkeelle pystyy lisäämään 
       halutun määrän hakukenttiä.</p>
  </dd>
  
  <dt><a name="Search Groups"></a>Hakuryhmät</dt>
  <dd>
    <p>Hakuryhmiä tarvitaan sellaisten kyselyiden laatimisessa, joissa pelkkien 
       hakukenttien yhdistely ei riitä. Jos haun kohteena on esimerkiksi Intian 
       tai Kiinan historia, tuottaa hakujen "Intia", "Kiina" ja "historia" 
       yhdistäminen <strong>Kaikilla termeillä (AND)</strong> –valinnalla 
       tuloksekseen vain kirjoja, joissa on käsitelty sekä Intiaa että Kiinaa. 
       Jos valintana on <strong>Millä tahansa termeistä (OR)</strong>, tulee 
       tulokseksi kaikki kirjat, joissa on käsitelty Kiinaa, Intiaa tai 
       historiaa.</p>
    <p>Hakuryhmien avulla voidaan määritellä hakukenttiä kokonaisuuksiksi ja 
       luoda kyselyitä näitä hyödyntäen. <strong>Lisää hakuryhmä</strong> –painike 
       lisää uuden ryhmän hakukenttiä, ja <strong>Poista hakuryhmä</strong> 
       –painikkeella ryhmiä voidaan poistaa. Hakuryhmien välisiä suhteita 
       määritellään käyttäen <strong>Kaikki ryhmät (AND)</strong> ja <strong>Mitkä 
       tahansa ryhmät (OR)</strong> –hakuoperaattoreita. Yllä olevan esimerkin 
       Intian tai Kiinan historiasta voi hakuryhmien avulla toteuttaa seuraavasti:</p>
    <ul>
      <li>Ensimmäisen hakuryhmän hakukenttiin lisätään termit "Intia" ja "Kiina" 
          ja määritellään hakukenttien välinen suhde <strong>Hae</strong>
          -alasvetovalikosta <strong>Millä tahansa termillä (OR)</strong>.</li>
      <li>Luodaan uusi hakuryhmä ja lisätään sen hakukenttään termi "historia". 
          Hakuryhmien väliseksi suhteeksi määritellään <strong>Kaikki ryhmät (AND)
          </strong>.</li>
    </ul>
  </dd>
</dl>

<p style="margin-top: 3em;"><a href="{$path}">&laquo; Etusivulle</a></p>
</div>

<!-- END of: Content/searchhelp.fi.tpl -->