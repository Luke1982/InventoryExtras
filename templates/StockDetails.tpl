<div class="slds-section" id="stockdetails-expandable-section">
	<h3 class="slds-section__title">
		<button
			aria-controls="stockdetails-expandable-sectioncontent"
			aria-expanded="true"
			class="slds-button slds-section__title-action"
			onclick="javascript:document.getElementById('stockdetails-expandable-section').classList.toggle('slds-is-open');setStockDetailsState();"
			type="button"
		>
			<svg class="slds-section__title-action-icon slds-button__icon slds-button__icon_left" aria-hidden="true">
				<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#switch"></use>
			</svg>
			<span class="slds-truncate" title="stockdetails">Gedetailleerde voorraad informatie</span>
		</button>
	</h3>
	<div aria-hidden="false" class="slds-section__content" id="stockdetails-expandable-sectioncontent">
		<div class="slds-box">
			<div class="slds-grid">
				<div class="slds-col slds-size_1-of-2">
					<div class="slds-grid" style="font-size: 1.1rem;">
						<div class="slds-col slds-size_10-of-12 slds-text-align_right">
							Aantal op voorraad
							<div style="display: inline-block; position: relative;">
								<button
									class="slds-button slds-button_icon slds-button slds-button_icon"
									aria-describedby="help"
									aria-disabled="true"
									title="Help"
									onMouseOver="javascript:this.nextElementSibling.style.display = 'block'"
									onMouseOut="javascript:this.nextElementSibling.style.display = 'none'"
								>
									<svg class="slds-button__icon" aria-hidden="true">
										<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#info"></use>
									</svg>
									<span class="slds-assistive-text">
										Meer informatie
									</span>
								</button>
								<div
									class="slds-popover slds-popover_tooltip slds-nubbin_bottom-left slds-text-align_left"
									role="tooltip"
									id="help"
									style="position: absolute; transform: translate3d(-2rem, calc(-100% - 2rem), 0); left: 15px; display: none; width:20rem">
										<div class="slds-popover__body" style="font-size: 0.8rem;">
											Dit aantal wordt berekend door alle aantallen
											'ontvangen' te nemen van alle inkooporders van
											dit product (dus de aantallen 'ontvangen' op
											de regels). Daar trekken we alle gefactureerde
											aantallen vanaf. Wat overblijft is de voorraad.
										</div>
								</div>
							</div>
						</div>
						<div class="slds-col slds-text-align_right slds-size_2-of-12" style="position: relative;">
							{$fields->value.qtyinstock|number_format:2:',':'.'}
						</div>
					</div>
					<div class="slds-grid" style="font-size: 1.1rem;">
						<div class="slds-col slds-size_10-of-12 slds-text-align_right">
							Aantal in order
							<div style="display: inline-block; position: relative;">
								<button
									class="slds-button slds-button_icon slds-button slds-button_icon"
									aria-describedby="help"
									aria-disabled="true"
									title="Help"
									onMouseOver="javascript:this.nextElementSibling.style.display = 'block'"
									onMouseOut="javascript:this.nextElementSibling.style.display = 'none'"
								>
									<svg class="slds-button__icon" aria-hidden="true">
										<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#info"></use>
									</svg>
									<span class="slds-assistive-text">
										Meer informatie
									</span>
								</button>
								<div
									class="slds-popover slds-popover_tooltip slds-nubbin_bottom-left slds-text-align_left"
									role="tooltip"
									id="help"
									style="position: absolute; transform: translate3d(-2rem, calc(-100% - 2rem), 0); left: 15px; display: none; width:20rem">
										<div class="slds-popover__body" style="font-size: 0.8rem;">
											Dit aantal berekenen we door alle orders te nemen
											die <b>niet</b> de status:
											<ul style="list-style-type: disc; margin-left: 1rem;">
											<li>Geleverd</li>
											<li>Geannuleerd</li>
											<li>Niet geleverd</li>
											</ul>
											hebben en die ook <b>niet</b> 'Voorraad mutatie
											buiten beschouwing laten' aan hebben staan. Als je
											bij één van die orders al wel een aantal 'geleverd'
											hebt ingevuld, wordt dit wel van het totaal afgetrokken.
										</div>
								</div>
							</div>
						</div>
						<div class="slds-col slds-text-align_right slds-size_2-of-12" style="position: relative;">
							-/-&nbsp;{$fields->value.invextras_prod_qty_in_order|number_format:2:',':'.'}
						</div>
					</div>
					<div class="slds-grid" style="font-size: 1.1rem; border-top: 2px solid black;">
						<div class="slds-col slds-size_10-of-12 slds-text-align_right">
							Beschikbare voorraad
							<div style="display: inline-block; position: relative;">
								<button
									class="slds-button slds-button_icon slds-button slds-button_icon"
									aria-describedby="help"
									aria-disabled="true"
									title="Help"
									onMouseOver="javascript:this.nextElementSibling.style.display = 'block'"
									onMouseOut="javascript:this.nextElementSibling.style.display = 'none'"
								>
									<svg class="slds-button__icon" aria-hidden="true">
										<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#info"></use>
									</svg>
									<span class="slds-assistive-text">
										Meer informatie
									</span>
								</button>
								<div
									class="slds-popover slds-popover_tooltip slds-nubbin_bottom-left slds-text-align_left"
									role="tooltip"
									id="help"
									style="position: absolute; transform: translate3d(-2rem, calc(-100% - 2rem), 0); left: 15px; display: none; width:20rem">
										<div class="slds-popover__body" style="font-size: 0.8rem;">
											Dit aantal is de 'vrije' voorraad. Zoveel zijn
											er beschikbaar als de aantallen die al zijn verkocht
											eenmaal zijn uitgeleverd. Een negatief aantal betekent
											dus dat je tekort komt als je alle openstaande orders
											moet uitleveren. Bestellen dus!
										</div>
								</div>
							</div>
						</div>
						<div class="slds-col slds-text-align_right slds-size_2-of-12" style="position: relative;">
							{$fields->value.invextras_prod_stock_avail|number_format:2:',':'.'}
						</div>
					</div>
				</div>
				<div class="slds-col slds-size_1-of-2 slds-p-left_medium">
					<div class="slds-grid" style="font-size: 1.1rem;">
						<div class="slds-col slds-size_10-of-12 slds-text-align_right">
							Aantal in order
							<div style="display: inline-block; position: relative;">
								<button
									class="slds-button slds-button_icon slds-button slds-button_icon"
									aria-describedby="help"
									aria-disabled="true"
									title="Help"
									onMouseOver="javascript:this.nextElementSibling.style.display = 'block'"
									onMouseOut="javascript:this.nextElementSibling.style.display = 'none'"
								>
									<svg class="slds-button__icon" aria-hidden="true">
										<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#info"></use>
									</svg>
									<span class="slds-assistive-text">
										Meer informatie
									</span>
								</button>
								<div
									class="slds-popover slds-popover_tooltip slds-nubbin_bottom-left slds-text-align_left"
									role="tooltip"
									id="help"
									style="position: absolute; transform: translate3d(-2rem, calc(-100% - 2rem), 0); left: 15px; display: none; width:20rem">
										<div class="slds-popover__body" style="font-size: 0.8rem;">
											Dit aantal berekenen we door alle orders te nemen
											die <b>niet</b> de status:
											<ul style="list-style-type: disc; margin-left: 1rem;">
											<li>Geleverd</li>
											<li>Geannuleerd</li>
											<li>Niet geleverd</li>
											</ul>
											hebben en die ook <b>niet</b> 'Voorraad mutatie
											buiten beschouwing laten' aan hebben staan. Als je
											bij één van die orders al wel een aantal 'geleverd'
											hebt ingevuld, wordt dit wel van het totaal afgetrokken.
										</div>
								</div>
							</div>
						</div>
						<div class="slds-col slds-text-align_right slds-size_2-of-12" style="position: relative;">
							{$fields->value.invextras_prod_qty_in_order|number_format:2:',':'.'}
						</div>
					</div>
					<div class="slds-grid" style="font-size: 1.1rem;">
						<div class="slds-col slds-size_10-of-12 slds-text-align_right">
							Minimale voorraad
							<div style="display: inline-block; position: relative;">
								<button
									class="slds-button slds-button_icon slds-button slds-button_icon"
									aria-describedby="help"
									aria-disabled="true"
									title="Help"
									onMouseOver="javascript:this.nextElementSibling.style.display = 'block'"
									onMouseOut="javascript:this.nextElementSibling.style.display = 'none'"
								>
									<svg class="slds-button__icon" aria-hidden="true">
										<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#info"></use>
									</svg>
									<span class="slds-assistive-text">
										Meer informatie
									</span>
								</button>
								<div
									class="slds-popover slds-popover_tooltip slds-nubbin_bottom-left slds-text-align_left"
									role="tooltip"
									id="help"
									style="position: absolute; transform: translate3d(-2rem, calc(-100% - 2rem), 0); left: 15px; display: none; width:20rem">
										<div class="slds-popover__body" style="font-size: 0.8rem;">
											Hoeveel wil je er minimaal op voorraad hebben.
										</div>
								</div>
							</div>
						</div>
						<div class="slds-col slds-text-align_right slds-size_2-of-12" style="position: relative;">
							+/+&nbsp;{$fields->value.reorderlevel|number_format:2:',':'.'}
						</div>
					</div>
					<div class="slds-grid" style="font-size: 1.1rem; border-top: 1px solid black;">
						<div class="slds-col slds-size_10-of-12 slds-text-align_right">
							Benodigd
							<div style="display: inline-block; position: relative;">
								<button
									class="slds-button slds-button_icon slds-button slds-button_icon"
									aria-describedby="help"
									aria-disabled="true"
									title="Help"
									onMouseOver="javascript:this.nextElementSibling.style.display = 'block'"
									onMouseOut="javascript:this.nextElementSibling.style.display = 'none'"
								>
									<svg class="slds-button__icon" aria-hidden="true">
										<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#info"></use>
									</svg>
									<span class="slds-assistive-text">
										Meer informatie
									</span>
								</button>
								<div
									class="slds-popover slds-popover_tooltip slds-nubbin_bottom-left slds-text-align_left"
									role="tooltip"
									id="help"
									style="position: absolute; transform: translate3d(-2rem, calc(-100% - 2rem), 0); left: 15px; display: none; width:20rem">
										<div class="slds-popover__body" style="font-size: 0.8rem;">
											Zoveel heb je er nodig om aan je orders te voldoen
											en je minimale voorraad op peil te houden.
										</div>
								</div>
							</div>
						</div>
						<div class="slds-col slds-text-align_right slds-size_2-of-12" style="position: relative;">
							{math 
								equation="reorderlevel + inorder" 
								reorderlevel=$fields->value.reorderlevel
								inorder=$fields->value.invextras_prod_qty_in_order
								assign='necessary'}
							{$necessary|number_format:2:',':'.'}
						</div>
					</div>
					<div class="slds-grid" style="font-size: 1.1rem;">
						<div class="slds-col slds-size_10-of-12 slds-text-align_right">
							Aantal op voorraad
							<div style="display: inline-block; position: relative;">
								<button
									class="slds-button slds-button_icon slds-button slds-button_icon"
									aria-describedby="help"
									aria-disabled="true"
									title="Help"
									onMouseOver="javascript:this.nextElementSibling.style.display = 'block'"
									onMouseOut="javascript:this.nextElementSibling.style.display = 'none'"
								>
									<svg class="slds-button__icon" aria-hidden="true">
										<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#info"></use>
									</svg>
									<span class="slds-assistive-text">
										Meer informatie
									</span>
								</button>
								<div
									class="slds-popover slds-popover_tooltip slds-nubbin_bottom-left slds-text-align_left"
									role="tooltip"
									id="help"
									style="position: absolute; transform: translate3d(-2rem, calc(-100% - 2rem), 0); left: 15px; display: none; width:20rem">
										<div class="slds-popover__body" style="font-size: 0.8rem;">
											Het aantal op voorraad trekken we
											weer van het aantal dat we nodig hebben
											af, want die hebben we al.
										</div>
								</div>
							</div>
						</div>
						<div class="slds-col slds-text-align_right slds-size_2-of-12" style="position: relative;">
							-/-&nbsp;{$fields->value.qtyinstock|number_format:2:',':'.'}
						</div>
					</div>
					<div class="slds-grid" style="font-size: 1.1rem;">
						<div class="slds-col slds-size_10-of-12 slds-text-align_right">
							Aantal in bestelling
							<div style="display: inline-block; position: relative;">
								<button
									class="slds-button slds-button_icon slds-button slds-button_icon"
									aria-describedby="help"
									aria-disabled="true"
									title="Help"
									onMouseOver="javascript:this.nextElementSibling.style.display = 'block'"
									onMouseOut="javascript:this.nextElementSibling.style.display = 'none'"
								>
									<svg class="slds-button__icon" aria-hidden="true">
										<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#info"></use>
									</svg>
									<span class="slds-assistive-text">
										Meer informatie
									</span>
								</button>
								<div
									class="slds-popover slds-popover_tooltip slds-nubbin_bottom-left slds-text-align_left"
									role="tooltip"
									id="help"
									style="position: absolute; transform: translate3d(-2rem, calc(-100% - 2rem), 0); left: 15px; display: none; width:20rem">
										<div class="slds-popover__body" style="font-size: 0.8rem;">
											Het aantal dat we al besteld hebben trekken we
											ook van het aantal dat we nodig hebben
											af, want die hebben we al besteld.
										</div>
								</div>
							</div>
						</div>
						<div class="slds-col slds-text-align_right slds-size_2-of-12" style="position: relative;">
							-/-&nbsp;{$fields->value.qtyindemand|number_format:2:',':'.'}
						</div>
					</div>
					<div class="slds-grid" style="font-size: 1.1rem; border-top: 2px solid black;">
						<div class="slds-col slds-size_10-of-12 slds-text-align_right">
							Aantal te bestellen
							<div style="display: inline-block; position: relative;">
								<button
									class="slds-button slds-button_icon slds-button slds-button_icon"
									aria-describedby="help"
									aria-disabled="true"
									title="Help"
									onMouseOver="javascript:this.nextElementSibling.style.display = 'block'"
									onMouseOut="javascript:this.nextElementSibling.style.display = 'none'"
								>
									<svg class="slds-button__icon" aria-hidden="true">
										<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#info"></use>
									</svg>
									<span class="slds-assistive-text">
										Meer informatie
									</span>
								</button>
								<div
									class="slds-popover slds-popover_tooltip slds-nubbin_bottom-left slds-text-align_left"
									role="tooltip"
									id="help"
									style="position: absolute; transform: translate3d(-2rem, calc(-100% - 2rem), 0); left: 15px; display: none; width:20rem">
										<div class="slds-popover__body" style="font-size: 0.8rem;">
											Dit aantal moeten we dus bestellen bovenop wat we
											al in bestelling hebben staan bij de leverancier,
											om te zorgen dat we aan de minimale voorraad en
											het aantal in order kunnen voldoen. Staat hier een
											negatief aantal? Dan heb je dus een overschot
											als alles binnen is dat je besteld hebt.
										</div>
								</div>
							</div>
						</div>
						<div class="slds-col slds-text-align_right slds-size_2-of-12" style="position: relative;">
							&nbsp;{$fields->value.invextras_prod_qty_to_order|number_format:2:',':'.'}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	/**
	* Saves the StockDetails open/close
	* state in the session
	*
	* @return none
	*/
	function setStockDetailsState() {
		if (document.getElementById('stockdetails-expandable-section').classList.contains('slds-is-open')) {
			localStorage.setItem('stockdetailsstate', 'open')
		} else {
			localStorage.setItem('stockdetailsstate', 'closed')
		}
	}
	window.addEventListener('load', () => {
		if (localStorage.getItem('stockdetailsstate')) {
			const cList = document.getElementById('stockdetails-expandable-section').classList;
			const method = localStorage.getItem('stockdetailsstate') === 'open' ? cList.add : cList.remove
			method.apply(cList, ['slds-is-open'])
		}
	});
</script>