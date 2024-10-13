<!-- page js -->
<script>
(function ($) {
    "use strict";

    var Dashboard = function () { };

    Dashboard.prototype.initCharts = function() {
        $(document).ready(function() {
            
            // Ambil data dari ajax_stats.php menggunakan AJAX
            $.ajax({
                url: 'ajax/ajax_stats.php', 
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Ambil data dari response
                    var purchaseValues = response.purchase_values;
                    var qtyValues = response.qty_values;
                    var affiliateCommissions = response.affiliate_commissions;

                    // Setup chart untuk today-revenue-chart (Purchase Values)
                    var options2 = {
                        chart: {
                            type: 'area',
                            height: 45,
                            width: 90,
                            sparkline: {
                                enabled: true
                            }
                        },
                        series: [{
                            data: purchaseValues
                        }],
                        stroke: {
                            width: 2,
                            curve: 'smooth'
                        },
                        markers: {
                            size: 0
                        },
                        colors: ["#727cf5"],
                        tooltip: {
                            fixed: {
                                enabled: false
                            },
                            x: {
                                show: false
                            },
                            y: {
                                title: {
                                    formatter: function (seriesName) {
                                        return ''
                                    }
                                }
                            },
                            marker: {
                                show: false
                            }
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                type: "vertical",
                                shadeIntensity: 1,
                                inverseColors: false,
                                opacityFrom: 0.45,
                                opacityTo: 0.05,
                                stops: [45, 100]
                            },
                        }
                    };

                    // Render chart untuk today-revenue-chart
                    new ApexCharts(document.querySelector("#today-revenue-chart"), options2).render();

                    // Setup dan render chart untuk today-product-sold-chart (Quantities)
                    new ApexCharts(document.querySelector("#today-product-sold-chart"), $.extend({}, options2, {
                        series: [{ data: qtyValues }],
                        colors: ['#f77e53']
                    })).render();

                    // Setup dan render chart untuk today-new-customer-chart (Affiliate Commissions)
                    new ApexCharts(document.querySelector("#today-new-customer-chart"), $.extend({}, options2, {
                        series: [{ data: affiliateCommissions }],
                        colors: ['#43d39e']
                    })).render();
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                }
            });

            // Variabel untuk menyimpan instance chart
            var chartPendapatannya;

            // Fungsi untuk memuat dan memperbarui data chart
            function loadChartData(periode) {
                $.ajax({
                    url: 'ajax/ajax_pendapatan.php',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        periode: periode // Kirim periode sebagai parameter
                    },
                    success: function(response) {
                        // Ambil data dari response
                        var totalKomisiValues = response.total_komisi_values;
                        var tanggalPendapatan = response.tanggal_values;
                        
                        // Konfigurasi chart
                        var options = {
                            chart: {
                                height: 329,
                                type: 'area'
                            },
                            dataLabels: {
                                enabled: false
                            },
                            stroke: {
                                curve: 'smooth',
                                width: 4
                            },
                            series: [{
                                name: 'Komisi Dibayarkan',
                                data: totalKomisiValues // Gunakan data dari response
                            }],
                            zoom: {
                                enabled: false
                            },
                            legend: {
                                show: false
                            },
                            colors: ['#43d39e'],
                            xaxis: {
                                type: 'category',
                                categories: tanggalPendapatan, // Gunakan kategori dari response
                                tooltip: {
                                    enabled: false
                                },
                                axisBorder: {
                                    show: false
                                }
                            },
                            yaxis: {
                                labels: {
                                    formatter: function (val) {
                                        return "Rp. " + val.toLocaleString('id-ID'); // Format dengan pemisah ribuan
                                    }
                                }
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    type: "vertical",
                                    shadeIntensity: 1,
                                    inverseColors: false,
                                    opacityFrom: 0.45,
                                    opacityTo: 0.05,
                                    stops: [45, 100]
                                }
                            }
                        };

                        // Jika chart sudah ada, hancurkan sebelum membuat yang baru
                        if (chartPendapatannya) {
                            chartPendapatannya.destroy();
                        }

                        // Render ulang chart dengan data baru
                        chartPendapatannya = new ApexCharts(
                            document.querySelector("#revenue-chart"),
                            options
                        );

                        chartPendapatannya.render();
                    },
                    error: function(error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }

            loadChartData();

            // Event listener untuk dropdown item
            $('.dropdown-item').on('click', function() {
                // Ambil periode yang dipilih dari data-periode
                var periode = $(this).data('periode');
                
                // Muat ulang data chart berdasarkan periode yang dipilih
                loadChartData(periode);
            });

            $.ajax({
                url: 'ajax/ajax_target.php', 
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Ambil data dari response
                    var Bulan = response.bulan;
                    var Target = response.target;

                    /* ------------- target */
                    var options = {
                        chart: {
                            height: 349,
                            type: 'bar',
                            stacked: true,
                            toolbar: {
                                show: false
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '45%',
                            },
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            show: true,
                            width: 2,
                            colors: ['transparent']
                        },
                        series: [{
                            name: 'Target',
                            data: Target
                        }],
                        xaxis: {
                            categories: Bulan,
                            axisBorder: {
                                show: false
                            },
                        },
                        yaxis: {
                            labels: {
                                formatter: function (val) {
                                    return "Rp. " + val.toLocaleString('id-ID'); // Format dengan pemisah ribuan
                                }
                            }
                        },
                        legend: {
                            show: false
                        },
                        grid: {
                            row: {
                                colors: ['transparent', 'transparent'], // takes an array which will be repeated on columns
                                opacity: 0.2
                            },
                            borderColor: '#f3f4f7'
                        },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return "Rp. " + val.toLocaleString('id-ID'); // Format dengan pemisah ribuan
                                }
                            }
                        }
                    }

                    var chart = new ApexCharts(
                        document.querySelector("#targets-chart"),
                        options
                    );

                    chart.render();
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                }
            });
        
        });
    },

    //initializing
    Dashboard.prototype.init = function () {
        // date picker
        $('#dash-daterange').flatpickr({
            mode: "range",
            defaultDate: [moment().subtract(7, 'days').format('YYYY-MM-DD'), moment().format('YYYY-MM-DD')]
        });

        // calendar
        $('#calendar-widget').flatpickr({
            inline: true,
            shorthandCurrentMonth: true,
        });

        // charts
        this.initCharts();
    };

    $.Dashboard = new Dashboard();
    $.Dashboard.Constructor = Dashboard;

})(window.jQuery);

//initializing main application module
(function ($) {
    "use strict";
    $.Dashboard.init();
})(window.jQuery);
</script>
