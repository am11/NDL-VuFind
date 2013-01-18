<!-- START of: Content/about.fi.tpl -->

{assign var="title" value="Finnan aineistojen käyttö"}
{capture append="sections"}{literal}

<p>Finnan käyttäjät voivat hakea tietoa arkistojen, kirjastojen ja museoiden aineistoista. Finnassa on: </p>

<p>
  <ul>
  	<li>haettavissa ja selattavissa aineistoja kuvailevaa tekstimuotoista metatietoa</li>
	<li>mikäli metatietoon liittyvä sisältö on digitaalisena verkossa, Finnassa on linkki sisältöä hallinnoivan organisaation sivustolle</li>
  </ul>
</p>

<p>Finnassa hakutulosten yhteydessä näytettävää metatietoa voivat kaikki käyttää vapaasti. Vuoden 2013 aikana metatiedolle ja sen käytölle valitaan lisenssi.</p>

<p>Finnasta pääsee linkkien kautta muille sivustoille, joilla oleviin sisältöihin voi liittyä lakiin tai sopimuksiin liittyviä oikeuksia tai rajoituksia. Näistä kerrotaan sisältöjä hallinnoivien organisaatioiden sivustoilla. </p>

<p>Joidenkin hakutulosten kohdalla Finnassa esitetään metatietoihin liittyvän digitaalisen sisällön kuva, esimerkiksi kuva museoesineestä, taideteoksesta, valokuvasta tai kirjan kannesta. Näihin ns. esikatselukuviin voi liittyä käytön rajoituksia samalla tavalla kuin muilla sivustoilla oleviin sisältöihin.</p>

{/literal}{/capture}
{include file="$module/content.tpl" title=$title sections=$sections}
<!-- END of: Content/about.fi.tpl -->