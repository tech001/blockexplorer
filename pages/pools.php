<style>
    #pools_rows > tr > td{
        vertical-align: middle;
        font-family: 'Inconsolata', monospace;
		text-align: center;
		white-space: pre-line;
    }

	#pools_header > tr > th:first-child,
	#pools_rows > tr > td:first-child {
		text-align: left;
	}

	#pools_header > tr > th:last-child,
	#pools_rows > tr > td:last-child {
		text-align: left;
	}
</style>

<div class="row">
	<div class="col-sm-12 col-md-6">
		<div class="panel panel-default" id="network-stats">
		  <div class="panel-heading">
			<h3 class="panel-title"><i class="fa fa-tachometer"></i> Stats</h3>
		  </div>
		  <div class="panel-body">
			<div class="row">
				<div class="col-sm-6 col-md-6">
					<ul class="nav nav-pills nav-stacked">
					
						<li><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="The overall hash rate of all known pools listed below."><i class="fa fa-tachometer"></i> Pools speed: <span id="networkHashrate"></span></a></li>
						
						<li><a href="#" data-toggle="tooltip" data-placement="bottom" data-original-title="Current estimated network hash rate. Calculated by current difficulty."><i class="fa fa-tachometer"></i> Network speed: <span id="totalPoolsHashrate"></span></a></li>
						
						<li><a href="#" data-toggle="tooltip" data-placement="bottom" data-original-title="Number of miners in all pools"><i class="fa fa-group"></i> Total miners: <span id="total_miners"></span></a></li>
						
						
					</ul>
				</div>
				<div class="col-sm-6 col-md-6">
					<ul class="nav nav-pills nav-stacked">
					
						<li><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="Difficulty for next block. Ratio at which at the current hashing speed blocks will be mined with 4 minutes interval."><i class="fa fa-unlock-alt"></i> Current difficulty: <span id="networkDifficulty"></span></a></li>
						
						<li><a href="#" data-toggle="tooltip" data-placement="bottom" data-original-title="Average difficulty by last 30 blocks."><i class="fa fa-lock"></i> Average difficulty: <span id="avgDifficulty"></span></a></li>
						
						<li><a href="#" data-toggle="tooltip" data-placement="bottom" data-original-title="Average estimated network hash rate. Calculated by average difficulty."><i class="fa fa-tachometer"></i> Average Hashrate: <span id="avgHashrate"></span></a></li>
						
					
					</ul>
				</div>
			</div>
			
		  </div>
		</div>
	</div>
	<div class="col-sm-12 col-md-6">
		<div class="panel panel-default" id="network-stats">
		  <div class="panel-heading">
			<h3 class="panel-title"><i class="fa fa-tachometer"></i> Estimate Mining Profits</h3>
		  </div>
		  <div class="panel-body">
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<div id="calcHashHolder">
						<div class="input-group">
							<input type="number" class="form-control" id="calcHashRate" placeholder="Enter your hash rate">
							<div class="input-group-btn">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" id="calcHashDropdown">
									<span id="calcHashUnit" data-mul="1">KH/s</span> <span class="caret"></span>
								</button>
								<ul class="dropdown-menu dropdown-menu-right" role="menu" id="calcHashUnits">
									<li><a href="#" data-mul="0">H/s</a></li>
									<li><a href="#" data-mul="1">KH/s</a></li>
									<li><a href="#" data-mul="2">MH/s</a></li>
								</ul>
							</div>
							<span class="input-group-addon">=</span>
							<span class="input-group-addon" id="calcHashResultsHolder"><span id="calcHashAmount"></span> <span id="calcHashSymbol"></span> UMK / day</span>
						</div>
					</div>
				</div>
			</div>
		  </div>	
		</div>
	</div>
</div>

<br />

<div class="table-responsive">
    <table id="network-hash" class="table table-hover sortable">
        <thead>
            <tr>
                <th><span id="symbol"></span> Pools</th>
                <th><i class="fa fa-bars"></i> Height</th>
                <th><i class="fa fa-tachometer"></i> Hashrate</th>
                <th><i class="fa fa-group"></i> Miners</th>
                <th><i class="fa fa-money"></i> Total Fee</th>
				<th><i class="fa fa-sign-out"></i> Min. Payout</th>
                <th><i class="fa fa-clock-o"></i> Last Block Found</th>
            </tr>
        </thead>
        <tbody id="pools_rows">
        </tbody>
    </table>
</div>

<div class="container">
        <div class="row">
			<div class="col-lg-6 col-md-6">
				<canvas id="poolsChart" style="margin: 0 auto;"></canvas>
			</div>
		</div>
</div>

<br />

<script src="/js/Chart.bundle.min.js"></script>
<script src="/js/sorttable.js"></script>
<script>
window.NETWORK_STAT_MAP = new Map(networkStat[symbol.toLowerCase()]);
window.NETWORK_STAT_MAP2 = new Map(networkStat2[symbol.toLowerCase()]);
window.poolNames = [];
window.poolHashrates = [];
window.colors = [];
Difficulties = [];
totalHashrate = 0;
totalMiners = 0;
lastReward = 0;
avgDiff = 0;

var poolsRefreshed = 0;
NETWORK_STAT_MAP.forEach((url, host, map) => {
    $.getJSON(url + '/stats', (data, textStatus, jqXHR) => {
        var d = new Date(parseInt(data.pool.lastBlockFound));
        var datestring = ("0" + d.getDate()).slice(-2) + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + d.getFullYear() + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);

        var agostring = $.timeago(d);
		
		var index = host.indexOf("/");
		var result;
		if (index < 0) {
			result = host;
		} else {
			result = host.substr(0, index);
		}

		$('#pools_rows').append('<tr><td id=host'+result+'><a target=blank href=http://'+host+'>'+result+'</a></td><td class="height" id=height-'+result+'>'+data.network.height+'</td><td id=hashrate-'+result+'>'+data.pool.hashrate+'&nbsp;H/s</td><td id=miners-'+result+'>'+data.pool.miners+'</td><td id=totalFree-'+result+'>'+calculateTotalFree(data)+'%</td><td id=minPayout-'+result+'>'+getReadableCoins(data.config.minPaymentThreshold,2)+'</td><td><span id=lastFound-'+result+'>'+datestring+'</span> (<span class="timeago" id="ago-'+result+'">'+agostring+'</span>)</td><</tr>');

        totalHashrate += parseInt(data.pool.hashrate);
		totalMiners += parseInt(data.pool.miners);
		updateText('totalPoolsHashrate', getReadableHashRateString(totalHashrate) + '/sec');
		updateText('total_miners', totalMiners);
		poolNames.push(result);
		poolHashrates.push(parseInt(data.pool.hashrate));
		window.colors.push(getRandomColor());

    });
	poolsRefreshed++;
	if (poolsRefreshed === NETWORK_STAT_MAP.size){
		setTimeout(function(){ displayChart(); }, 1000);
	}
});

NETWORK_STAT_MAP2.forEach((url, host, map) => {
	var index = host.indexOf("/");
	var result;
	if (index < 0) {
		result = host;
	} else {
		result = host.substr(0, index);
	}

	$.getJSON(url + '/pool/stats', (data, textStatus, jqXHR) => {
		var d = new Date(data.pool_statistics.lastBlockFoundTime*1000);

        var datestring = ("0" + d.getDate()).slice(-2) + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + d.getFullYear() + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);

        var agostring = $.timeago(d);

        $('#pools_rows').append('<tr><td id=host'+result+'><a target=blank href=http://'+host+'>'+result+'</a></td><td class="height" id=height-'+result+'>'+'</td><td id=hashrate-'+result+'>'+data.pool_statistics.hashRate+'&nbsp;H/s</td><td id=miners-'+result+'>'+data.pool_statistics.miners+'</td><td id=totalFree-'+result+'>'+'</td><td id=minPayout-'+result+'>'+'</td><td><span id=lastFound-'+result+'>'+datestring+'</span> (<span class="timeago" id="ago-'+result+'">'+agostring+'</span>)</td><</tr>');

        totalHashrate += parseInt(data.pool_statistics.hashRate);
		totalMiners += parseInt(data.pool_statistics.miners);
		updateText('totalPoolsHashrate', getReadableHashRateString(totalHashrate) + '/sec');
		updateText('total_miners', totalMiners);
		poolNames.push(result);
		poolHashrates.push(parseInt(data.pool_statistics.hashRate));
		window.colors.push(getRandomColor());

        $.getJSON(url + '/network/stats', (data, textStatus, jqXHR) => {
			updateText('height-'+result, data.height);
		});

        $.getJSON(url + '/config', (data, textStatus, jqXHR) => {
			updateText('totalFree-'+result, "PPLNS: "+data.pplns_fee+"%,\nPPS: "+data.pps_fee+"%,\nSolo: "+data.solo_fee+"%");
			updateText('minPayout-'+result, "Wallet: "+getReadableCoins(data.min_wallet_payout,2)+",\nExchange: "+getReadableCoins(data.min_exchange_payout,2));
		});
	});


    poolsRefreshed++;
	if (poolsRefreshed === NETWORK_STAT_MAP2.size){
		setTimeout(function(){ displayChart(); }, 1000);
	}
});

    currentPage = {
		destroy: function(){
		},
        init: function(){
			getBlocks();
			renderLastBlock();
		},
		update: function(){
			updateText('networkHashrate', getReadableHashRateString(lastStats.difficulty / blockTargetInterval) + '/sec');
			updateText('networkDifficulty', getReadableDifficultyString(lastStats.difficulty, 0).toString());
			getBlocks();
			renderLastBlock();
		}
	};

function calculateTotalFree(config) {
	let totalFee = config.config.fee;
    for (let property in config.config.donation) {
        if (config.config.donation.hasOwnProperty(property)) {
            totalFee += config.config.donation[property];
        }
    }
    return totalFee;
}

function displayChart() {
    var ctx = document.getElementById("poolsChart");

    var chartData = {
		labels: poolNames,
		datasets: [{
			data: poolHashrates,
			backgroundColor: colors,
			borderWidth: 1,
			segmentShowStroke: false
		}]
	};
	var options = {
		title: {
			display: true,
			text: 'Known pools hash rate'
		},
		tooltips: {
			enabled: true,
			mode: 'single',
			callbacks: {
				title: function (tooltipItem, data) { return data.labels[tooltipItem[0].index]; },
				label: function (tooltipItem, data) {
					var amount = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
					var total = eval(data.datasets[tooltipItem.datasetIndex].data.join("+"));
					return amount + ' / ' + total + ' H/s  (' + parseFloat(amount * 100 / total).toFixed(2) + '%)';
				}
			}
		}
	};

	window.poolsChart = new Chart(ctx,{
		type: 'pie',
		data: chartData,
		options: options
	});
}

setInterval(function(){
			
	var totalHashrate = 0;
	totalMiners = 0;
	poolNames = [];
	poolHashrates = [];
	var poolsRefreshed = 0;
		
            NETWORK_STAT_MAP.forEach((url, host, map) => {
				
				var index = host.indexOf("/");
				var result;
				if (index < 0) {
					result = host;
				} else {
					result = host.substr(0, index);
				}
					
                $.getJSON(url + '/stats', (data, textStatus, jqXHR) => {
                    updateText('height-'+result, data.network.height);
                    updateText('hashrate-'+result, data.pool.hashrate);
                    updateText('miners-'+result, data.pool.miners);
                    
					var d = new Date(parseInt(data.pool.lastBlockFound));
					
					var datestring = ("0" + d.getDate()).slice(-2) + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + d.getFullYear() + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
                    updateText('lastFound'+result, datestring);
					 
                    var agostring = $.timeago(d);
					updateText('ago-'+result, agostring);
					
					totalHashrate += parseInt(data.pool.hashrate);
					totalMiners += parseInt(data.pool.miners);
					updateText('totalPoolsHashrate', getReadableHashRateString(totalHashrate) + '/sec');
					updateText('total_miners', totalMiners);
					updateText('networkHashrate', getReadableHashRateString(lastStats.difficulty / blockTargetInterval) + '/sec');
					updateText('networkDifficulty', getReadableDifficultyString(lastStats.difficulty, 0).toString());
                });
				poolsRefreshed++;
				if (poolsRefreshed === NETWORK_STAT_MAP.size){ 
					setTimeout(function(){ refreshChart(); }, 1000);
				}	

            });
			NETWORK_STAT_MAP2.forEach((url, host, map) => {
			
				var index = host.indexOf("/");
				var result;
				if (index < 0) {
					result = host;
				} else {
					result = host.substr(0, index);
				}
				
				
				$.getJSON(url + '/pool/stats', (data, textStatus, jqXHR) => {
					var d = new Date(data.pool_statistics.lastBlockFoundTime*1000);
					var datestring = ("0" + d.getDate()).slice(-2) + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + d.getFullYear() + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
					
					var agostring = $.timeago(d);
					
                    updateText('hashrate-'+result, data.pool_statistics.hashRate);
                    updateText('miners-'+result, data.pool_statistics.miners);
                    //updateText('totalFree'+result, calculateTotalFree(data)+'%');
				
					totalHashrate += parseInt(data.pool_statistics.hashRate);
					totalMiners += parseInt(data.pool_statistics.miners);
					updateText('totalPoolsHashrate', getReadableHashRateString(totalHashrate) + '/sec');
					updateText('total_miners', totalMiners);
					
				});
				$.getJSON(url + '/network/stats', (data, textStatus, jqXHR) => {
				   updateText('height-'+result, data.height);
				});
				
				poolsRefreshed++;
					if (poolsRefreshed === NETWORK_STAT_MAP2.size){ 
						setTimeout(function(){ displayChart(); }, 1000);
					}
			});

}, 240000);


function refreshChart() {
	var pool_rows = $('#pools_rows').children();
		for (var i = 0; i < pool_rows.length; i++) {
			var row = $(pool_rows[i]);
			var label = row.find('td:first').text();
			var hashrate = 	row.find('td:nth-child(3)').text();
			poolsChart.data.labels[i] = label;
			poolsChart.data.datasets[0].data[i] = parseInt(hashrate);
		}
		poolsChart.update();
}	
	

function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

$(function() {
    $('[data-toggle="tooltip"]').tooltip();
});


var xhrGetBlocks;
function getBlocks() {
    if (xhrGetBlocks) xhrGetBlocks.abort();
        xhrGetBlocks = $.ajax({
            url: api + '/json_rpc',
            method: "POST",
            data: JSON.stringify({
                jsonrpc:"2.0",
                id: "test",
                method:"f_blocks_list_json",
                params: {
                    height: lastStats.height - 1
                }
            }),
            dataType: 'json',
            cache: 'false',
            success: function(data){
				$.when(
					 renderBlocks(data.result.blocks)
				).then(function() {
					setTimeout(function(){
						calcAvgHashRate();
					}, 100)
				});
            }
    })
}

function renderBlocks(blocksResults){
        for (var i = 0; i < blocksResults.length; i ++){
            var block = blocksResults[i];
			Difficulties.push(parseInt(block.difficulty));
        }
		
}

function calcAvgHashRate(){
		var sum = Difficulties.reduce(add, 0);
		function add(a, b) {
			return a + b;
		}
		avgDiff = Math.round(sum / Difficulties.length);
		var avgHashRate = avgDiff / blockTargetInterval;
		
		updateText('avgDifficulty', getReadableDifficultyString(avgDiff, 0).toString());
		updateText('avgHashrate', getReadableHashRateString(avgDiff / blockTargetInterval));
        //updateText('blockSolveTime', getReadableTime(lastStats.difficulty / avgHashRate));
}

function renderLastBlock(){
$.ajax({
    url: api + '/json_rpc',
    method: "POST",
    data: JSON.stringify({
          jsonrpc:"2.0",
          id: "test",
          method:"getlastblockheader",
          params: {
  
                }
    }),
    dataType: 'json',
    cache: 'false',
    success: function(data){
		last_block_hash = data.result.block_header.hash;
		$.ajax({
			url: api + '/json_rpc',
			method: "POST",
			data: JSON.stringify({
				jsonrpc:"2.0",
				id: "test",
				method:"f_block_json",
				params: {
				    hash: last_block_hash
				}
			}),
			dataType: 'json',
			cache: 'false',
			success: function(data){
				var block = data.result.block;
				lastReward = parseInt(block.baseReward);
			}
		});
    }
});
}


    /* Hash Profitability Calculator */

    $('#calcHashRate').keyup(calcEstimateProfit).change(calcEstimateProfit);

    $('#calcHashUnits > li > a').click(function(e){
        e.preventDefault();
        $('#calcHashUnit').text($(this).text()).data('mul', $(this).data('mul'));
        calcEstimateProfit();
    });

	
	function calcEstimateProfit(){
        try {
            var rateUnit = Math.pow(1024,parseInt($('#calcHashUnit').data('mul')));
            var hashRate = parseFloat($('#calcHashRate').val()) * rateUnit;
            var profit = (hashRate * 86400 / avgDiff /*lastStats.difficulty*/) * lastReward;
            if (profit) {
                updateText('calcHashAmount', getReadableCoins(profit, 2, true));
                return;
            }
        }
        catch(e){ }
        updateText('calcHashAmount', '');
    }

</script>