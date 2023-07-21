<!-- Main Content -->
<div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="row">
          <!-- Ingresos de cafeteria -->
          <div class="col-12 col-sm-6 col-lg-3">
            <div class="card gradient-bottom">
              <div class="card-header">
                <h5 style="font-size: 14px;">Cafetería</h5>

              </div>
              <div class="card-body" id="top-5-scroll" tabindex="2" style="min-height: 255px;max-height: 255px; overflow: hidden; outline: none;">
                <ul class="list-unstyled list-unstyled-border">
                  <li class="media" style="border-bottom: solid 1px #F39865;">
                    <div class="media-body" style="cursor: pointer" >
                      
                    <div class="media-title">EFECTIVO</div>
                      <div class="mt-1">
                        <div class="budget-price">
                          <div class="budget-price-square bg-warning" data-width="10%" style="width: 10%;"></div>
                          <div class="budget-price-label">CRC <?php echo e(number_format($data['resumen']['totalIngresosEfectivoCafeteria']  ?? '0.00',2,".",",")); ?></div>
                        </div>
                        
                      </div>
                    </div>
                    
                  </li>

                  <li class="media" style="border-bottom: solid 1px #F39865;">
                    <div class="media-body" style="cursor: pointer" >
                      
                    <div class="media-title">TARJETAS</div>
                      <div class="mt-1">
                        <div class="budget-price">
                          <small style="margin-right: 5px;">SubTotal</small>
                          <div class="budget-price-square bg-warning" data-width="10%" style="width: 10%;"></div>
                          <div class="budget-price-label">CRC <?php echo e(number_format($data['resumen']['totalIngresosTarjetaCafeteria']  ?? '0.00',2,".",",")); ?></div>
                        </div>
                        <div class="budget-price">
                          <small style="margin-right: 5px;">Cobro banco </small>
                          <div class="budget-price-square bg-danger" data-width="10%" style="width: 10%;"></div>
                          <div class="budget-price-label">CRC <?php echo e(number_format($data['resumen']['totalPagoTarjetaCafeteria']  ?? '0.00',2,".",",")); ?></div>
                        </div>
                        <div class="budget-price">
                          <small style="margin-right: 5px;">Total </small>
                          <div class="budget-price-square bg-warning" data-width="10%" style="width: 10%;"></div>
                          <div class="budget-price-label" >CRC <?php echo e(number_format($data['resumen']['ingresosCafeteriaTarjetaImpuestoAplicado']  ?? '0.00',2,".",",")); ?></div>
                        </div>
                        
                      </div>
                    </div>
                    
                  </li>

                  <li class="media" >
                    <div class="media-body" style="cursor: pointer" >
                      
                    <div class="media-title">SINPE</div>
                      <div class="mt-1">
                        <div class="budget-price">
                          <div class="budget-price-square bg-warning" data-width="10%" style="width: 10%;"></div>
                          <div class="budget-price-label" style="color: black">CRC <?php echo e(number_format($data['resumen']['totalIngresosSinpeCafeteria']  ?? '0.00',2,".",",")); ?></div>
                        </div>
                        
                      </div>
                    </div>
                    
                  </li>  
                  
                </ul>
              </div>
              <div class="card-footer pt-3 d-flex justify-content-center">

               
              </div>
            </div>
          </div>
           <!-- Gaatos Cafeteria-->
           <div class="col-12 col-sm-6 col-lg-3">
            <div class="card">
              <div class="card-body card-type-3">
                <div class="row">
                  <div class="col">
                    <h6 class="text-muted mb-0">Gastos Cafetería</h6>
                    <span class="font-weight-bold mb-0">CRC <?php echo e(number_format($data['resumen']['gastosCafeteria'] ?? '0.00',2,".",",")); ?></span>
                  </div>
                  <div class="col-auto">
                    <div class="card-circle l-bg-orange text-white">
                      <i class="fa fa-coffee "></i>
                    </div>
                  </div>
                </div>
                <p class="mt-3 mb-0 text-muted text-sm">
        
                  <span class="text-nowrap">Mes <?php echo e($data['resumen']['mesActual'] ?? 'Actual'); ?></span>
                </p>
              </div>
            </div>
          </div>
          <!-- Ingresos de mes -->
          <div class="col-12 col-sm-6 col-lg-3">
            <div class="card gradient-bottom">
              <div class="card-header">
                <h5 style="font-size: 14px;">Ingresos del mes</h5>

              </div>
              <div class="card-body" id="top-5-scroll" tabindex="2" style="min-height: 255px;max-height: 255px; overflow: hidden; outline: none;">
                <ul class="list-unstyled list-unstyled-border">
                  <li class="media" style="border-bottom: solid 1px #F39865;">
                    <div class="media-body" style="cursor: pointer" >
                      
                    <div class="media-title">EFECTIVO</div>
                      <div class="mt-1">
                        <div class="budget-price">
                          <div class="budget-price-square bg-warning" data-width="10%" style="width: 10%;"></div>
                          <div class="budget-price-label">CRC <?php echo e(number_format($data['resumen']['totalIngresosEfectivo']  ?? '0.00',2,".",",")); ?></div>
                        </div>
                        
                      </div>
                    </div>
                    
                  </li>

                  <li class="media" style="border-bottom: solid 1px #F39865;">
                    <div class="media-body" style="cursor: pointer" >
                      
                    <div class="media-title">TARJETAS</div>
                      <div class="mt-1">
                        <div class="budget-price">
                          <small style="margin-right: 5px;">SubTotal</small>
                          <div class="budget-price-square bg-warning" data-width="10%" style="width: 10%;"></div>
                          <div class="budget-price-label">CRC <?php echo e(number_format($data['resumen']['totalIngressosTarjeta']  ?? '0.00',2,".",",")); ?></div>
                        </div>
                        <div class="budget-price">
                          <small style="margin-right: 5px;">Cobro banco</small>
                          <div class="budget-price-square bg-danger" data-width="10%" style="width: 10%;"></div>
                          <div class="budget-price-label">CRC <?php echo e(number_format($data['resumen']['totalPagoTarjeta']  ?? '0.00',2,".",",")); ?></div>
                        </div>
                        <div class="budget-price">
                          <small style="margin-right: 5px;">Total</small>
                          <div class="budget-price-square bg-warning" data-width="10%" style="width: 10%;"></div>
                          <div class="budget-price-label">CRC <?php echo e(number_format($data['resumen']['ingresosTarjetaImpuestoAplicado']  ?? '0.00',2,".",",")); ?></div>
                        </div>
                        
                      </div>
                    </div>
                    
                  </li>

                  <li class="media" >
                    <div class="media-body" style="cursor: pointer" >
                      
                    <div class="media-title">SINPE</div>
                      <div class="mt-1">
                        <div class="budget-price">
                          <div class="budget-price-square bg-warning" data-width="10%" style="width: 10%;"></div>
                          <div class="budget-price-label">CRC <?php echo e(number_format($data['resumen']['totalIngresosSinpe']  ?? '0.00',2,".",",")); ?></div>
                        </div>
                        
                      </div>
                    </div>
                    
                  </li>  
                  
                </ul>
              </div>
              <div class="card-footer pt-3 d-flex justify-content-center">
                <div class="row">
                  
                  <div class="col-12 col-sm-12 col-lg-12">
                    <div class="budget-price justify-content-center">
                      <div class="budget-price-label" style="margin-right: 5px;">Total</div>
                      <div class="budget-price-square bg-primary" data-width="20" style="width: 20px;"></div>
                      <div class="budget-price-label">CRC <?php echo e(number_format($data['resumen']['subTotalFondos'] ?? '0.00',2,".",",")); ?></div>
                    </div>
                  </div>
                </div>
               
              </div>
            </div>
          </div>
          <!-- Gaatos -->
          <div class="col-12 col-sm-6 col-lg-3">
            <div class="card">
              <div class="card-body card-type-3">
                <div class="row">
                  <div class="col">
                    <h6 class="text-muted mb-0">Gastos</h6>
                    <span class="font-weight-bold mb-0">CRC <?php echo e(number_format($data['resumen']['gastos'] ?? '0.00',2,".",",")); ?></span>
                  </div>
                  <div class="col-auto">
                    <div class="card-circle l-bg-orange text-white">
                      <i class="fa fa-money-bill-alt"></i>
                    </div>
                  </div>
                </div>
                <p class="mt-3 mb-0 text-muted text-sm">
        
                  <span class="text-nowrap">Mes <?php echo e($data['resumen']['mesActual'] ?? 'Actual'); ?></span>
                </p>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-md-12 col-lg-4">
            
          </div>
          

      </div>
    </section>

  </div><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/main/admin.blade.php ENDPATH**/ ?>