<template id="terrain-compass-template">
  <div class="container terrain-compass-container">
    <div id="kompas-muka-bumi" class="terrain-compass" role="group" aria-labelledby="terrain-compass-title">
      <div class="terrain-compass-heading">
        <div>
          <span class="md-eyebrow">Terokai Ranau</span>
          <h3 id="terrain-compass-title">Arah &amp; bentuk muka bumi</h3>
        </div>
        <span class="terrain-compass-location">Ranau, Sabah</span>
      </div>
      <div class="terrain-compass-layout">
        <figure class="terrain-compass-figure">
          <div class="terrain-compass-stage" aria-hidden="true">
            <svg class="terrain-compass-contours" viewBox="0 0 480 360" fill="none">
              <path d="M-30 160C50 60 90 225 172 145S300 20 375 100 450 120 520 25M-30 185C50 85 90 250 172 170S300 45 375 125 450 145 520 50M-30 210C50 110 90 275 172 195S300 70 375 150 450 170 520 75M-30 235C50 135 90 300 172 220S300 95 375 175 450 195 520 100M-30 260C50 160 90 325 172 245S300 120 375 200 450 220 520 125M-30 285C50 185 90 350 172 270S300 145 375 225 450 245 520 150"/>
            </svg>
            <div class="terrain-compass-shadow"></div>
            <div class="terrain-compass-instrument">
              <div class="terrain-compass-case"></div>
              <div class="terrain-compass-face">
                <div class="terrain-compass-ticks"></div>
                <svg class="terrain-compass-rose" viewBox="0 0 320 320" fill="none">
                  <circle cx="160" cy="160" r="112" stroke="currentColor" stroke-opacity=".18"/>
                  <circle cx="160" cy="160" r="91" stroke="currentColor" stroke-opacity=".1"/>
                  <path d="M160 48 177 143 272 160 177 177 160 272 143 177 48 160 143 143Z" fill="currentColor" fill-opacity=".07"/>
                  <path d="M160 76V244M76 160H244M101 101 219 219M101 219 219 101" stroke="currentColor" stroke-opacity=".14"/>
                  <path d="M160 100 169 151 211 109 169 169 220 160 169 178 211 211 160 178 109 211 151 169 100 160 151 151 109 109 160 151Z" stroke="currentColor" stroke-opacity=".12"/>
                </svg>
                <span class="terrain-compass-mark terrain-compass-north is-active" data-compass-mark="north">U</span>
                <span class="terrain-compass-mark terrain-compass-east" data-compass-mark="east">T</span>
                <span class="terrain-compass-mark terrain-compass-south" data-compass-mark="south">S</span>
                <span class="terrain-compass-mark terrain-compass-west">B</span>
                <span class="terrain-compass-intercardinal terrain-compass-ne">45</span>
                <span class="terrain-compass-intercardinal terrain-compass-se">135</span>
                <span class="terrain-compass-intercardinal terrain-compass-sw">225</span>
                <span class="terrain-compass-intercardinal terrain-compass-nw">315</span>
              </div>
              <div class="terrain-compass-needle">
                <svg viewBox="0 0 320 320" fill="none">
                  <path d="M160 60 184 160 160 149Z" fill="#e9c879"/>
                  <path d="M160 60 160 149 136 160Z" fill="#aa7825"/>
                  <path d="M160 260 136 160 160 171Z" fill="#344c66"/>
                  <path d="M160 260 160 171 184 160Z" fill="#152b42"/>
                </svg>
              </div>
              <div class="terrain-compass-pivot"></div>
            </div>
          </div>
          <figcaption>
            <span class="terrain-compass-status" aria-live="polite" aria-atomic="true">Utara · 000°</span>
            <span class="terrain-compass-scale">Ilustrasi arah · tidak mengikut skala</span>
          </figcaption>
        </figure>
        <div class="terrain-compass-directions">
          <p class="terrain-compass-instruction">Pilih arah untuk menggerakkan jarum kompas.</p>
          <div class="terrain-compass-options" aria-label="Pilih arah muka bumi">
            <button type="button" class="terrain-compass-direction" data-compass-direction="north" aria-pressed="true">
              <span class="terrain-compass-letter" aria-hidden="true">U</span>
              <span><strong data-direction-label>Utara</strong><span data-direction-terrain></span></span>
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
            </button>
            <button type="button" class="terrain-compass-direction" data-compass-direction="east" aria-pressed="false">
              <span class="terrain-compass-letter" aria-hidden="true">T</span>
              <span><strong data-direction-label>Timur</strong><span data-direction-terrain></span></span>
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
            <button type="button" class="terrain-compass-direction" data-compass-direction="south" aria-pressed="false">
              <span class="terrain-compass-letter" aria-hidden="true">S</span>
              <span><strong data-direction-label>Selatan</strong><span data-direction-terrain></span></span>
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </button>
          </div>
          <p class="terrain-compass-river">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 7c2-3 5 3 8 0s6 3 10 0M2 12c2-3 5 3 8 0s6 3 10 0M2 17c2-3 5 3 8 0s6 3 10 0"/></svg>
            <span><span data-river-label>Saliran utama</span><strong data-river-name></strong></span>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
