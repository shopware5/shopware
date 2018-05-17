<div class="col-50 devices--industry industry-disabled">
    <div class="disabled-notice" v-html="$t('disabledText')"></div>
    <div class="target-group-graph-industry" data-target-group-chart="true" data-industry="true"></div>

    <div class="row">
        <h4>[[ $t('industryTitle') ]]</h4>
        <div class="data--txt col-80">
            <div class="devices-graph-list-wrapper">
                <div class="devices-item">
                    <div class="shop-color col-66">tablet</div>
                    <div class="high-contrast col-33">7.55%</div>
                </div>
                <div class="devices-item">
                    <div class="shop-color col-66">mobile</div>
                    <div class="high-contrast col-33">0.28%</div>
                </div>
                <div class="devices-item">
                    <div class="shop-color col-66">desktop</div>
                    <div class="high-contrast col-33">92.18%</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-50 devices--shop">
    <div class="target-group-graph-shop" data-target-group-chart="true" data-industry="false"></div>
    <div class="row">
        <h4>[[ $t('shopTitle') ]]</h4>
        <div class="data--txt col-80">
            <devices-graph-list-wrapper></devices-graph-list-wrapper>
        </div>
    </div>
</div>