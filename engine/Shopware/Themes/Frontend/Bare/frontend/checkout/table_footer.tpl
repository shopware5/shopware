<footer class="table--footer block-group">

	{* Benefits *}
	{block name="frontend_checkout_footer_benefits"}
		<div class="footer--benefit block">
			{block name="frontend_checkout_footer_headline_benefit"}
				<h4 class="benefit--headline">{s name="CheckoutFooterBenefitHeadlineForYou"}Unserer Vorteil für Sie{/s}</h4>
			{/block}

			{block name="frontend_checkout_footer_benefits_list"}
				<ul class="list--unstyled benefit--list">

					{block name="frontend_checkout_footer_benefits_list_entry_1"}
						<li class="list--entry">
							<i class="icon--check"></i>
							{s name='RegisterInfoAdvantagesEntry1' namespace="frontend/register/index"}{/s}
						</li>
					{/block}

					{block name="frontend_checkout_footer_benefits_list_entry_2"}
						<li class="list--entry">
							<i class="icon--check"></i>
							{s name='RegisterInfoAdvantagesEntry2' namespace="frontend/register/index"}{/s}
						</li>
					{/block}

					{block name="frontend_checkout_footer_benefits_list_entry_3"}
						<li class="list--entry">
							<i class="icon--check"></i>
							{s name='RegisterInfoAdvantagesEntry3' namespace="frontend/register/index"}{/s}
						</li>
					{/block}

					{block name="frontend_checkout_footer_benefits_list_entry_4"}
						<li class="list--entry">
							<i class="icon--check"></i>
							{s name='RegisterInfoAdvantagesEntry4' namespace="frontend/register/index"}{/s}
						</li>
					{/block}
				</ul>
			{/block}
		</div>
	{/block}

	{* Supported dispatch services *}
	{block name="frontend_checkout_footer_dispatch"}
		<div class="footer--benefit block">
			{block name="frontend_checkout_footer_headline_dispatch"}
				<h4 class="benefit--headline">{s name="CheckoutFooterBenefitHeadlineDispatch"}Wir verschicken mit{/s}</h4>
			{/block}

			{block name="frontend_checkout_footer_text_dispatch"}
				<p class="benefit--text">
					{s name="CheckoutFooterBenefitTextDispatch"}Ihre Versandanbieter-Logos{/s}
				</p>
			{/block}
		</div>
	{/block}

	{* Supported payment services *}
	{block name="frontend_checkout_footer_payment"}
		<div class="footer--benefit block">
			{block name="frontend_checkout_footer_headline_payment"}
				<h4 class="benefit--headline">{s name="CheckoutFooterBenefitHeadlinePayment"}Zahlungsmethoden{/s}</h4>
			{/block}

			{block name="frontend_checkout_footer_text_payment"}
				<p class="benefit--text">
					{s name="CheckoutFooterBenefitTextPayment"}Ihre Zahlungsanbieter-Logos{/s}
				</p>
			{/block}
		</div>
	{/block}
</footer>