<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<script>
  var gastosCaja = "<?php echo e($data['total_gastos_caja']); ?>";
</script>
 <!-- Main Content -->
 <div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="row">
          <div class="col-12 col-md-12 col-lg-12">
            <div class="card">
              <form  action="<?php echo e(URL::to('caja/cerrarcaja')); ?>"  method="POST">
                <?php echo e(csrf_field()); ?>


                <div class="card-header">
                <h4>Cierre Caja - <?php echo e(session('usuario')['nombre']); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                      <!-- cierre caja -->
                      <div class="col-12 col-md-12 col-lg-8">
                        <div class="row">
                          <div class="col-sm-12 col-md-6 col-xl-4">
                            <div class="form-group">
                              <label>Fecha Cierre </label>
                            <input type="date" id="fecha" name="fecha" max='<?php echo e(date('Y-m-d')); ?>' class="form-control" >
                            </div>
                          </div>
                          <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Monto Efectivo (CRC)</label>
                                <input type="number" readonly class="form-control" step=any id="monto_efectivo" name="monto_efectivo" value="<?php echo e($data['datos']['monto_efectivo'] ??""); ?>" placeholder="0.00" onkeyup='calcularCaja("<?php echo e($data["total_gastos_caja"]); ?>")' min="0">
                              </div>
                          </div>

                          <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Monto Tarjeta (CRC)</label>
                                <input type="number" class="form-control" step=any id="monto_tarjeta" name="monto_tarjeta" value="<?php echo e($data['datos']['monto_tarjeta'] ??""); ?>" placeholder="0.00" onkeyup='calcularCaja("<?php echo e($data["total_gastos_caja"]); ?>")' min="0">
                              </div>
                          </div>

                          <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Monto SINPE (CRC)</label>
                            <input type="number" class="form-control" step="any" id="monto_sinpe" name="monto_sinpe" value="<?php echo e($data['datos']['monto_sinpe'] ??""); ?>" placeholder="0.00" onkeyup='calcularCaja("<?php echo e($data["total_gastos_caja"]); ?>")' min="0">
                              </div>
                          </div>
    
                          <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                              <label>Turno</label>
                              <select class="form-control" name="turno">
                                <option value="M" <?php if ($data['datos']['turno'] ?? 'M' == 'M'){ echo 'selected';} ?>>Mañana</option>
                                <option value="T" <?php if ($data['datos']['turno'] ?? 'M' == 'T'){ echo 'selected';} ?>>Tarde</option>
                              </select>
                            </div>
                          </div>
  
                          <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-group">
                                  <label>Observación</label>
                                  <textarea class="form-control" name="observacion" maxlength="150"><?php echo e($data['datos']['observacion'] ??""); ?></textarea>
                                </div>
                          </div>
                          <div class="col-12 col-md-12 col-lg-12">
                            <div class="form-group">
                              <h5> Desgloce de monto en efectivo por tipo de valor</h5>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                          <div class="form-group">
                              <label>Cantidad de MONEDAS CRC 5 </label>
                          <input type="number" class="form-control"  id="efectivo_mon_5" name="efectivo_mon_5" value="<?php echo e($data['datos']['efectivo_mon_5'] ??"0"); ?>" placeholder="0" onkeyup='calcularMontoEfectivo()' min="0">
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                          <div class="form-group">
                              <label>Cantidad de MONEDAS CRC 10 </label>
                          <input type="number" class="form-control"  id="efectivo_mon_10" name="efectivo_mon_10" value="<?php echo e($data['datos']['efectivo_mon_10'] ??"0"); ?>" placeholder="0" onkeyup='calcularMontoEfectivo()' min="0">
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                          <div class="form-group">
                              <label>Cantidad de MONEDAS CRC 25 </label>
                          <input type="number" class="form-control"  id="efectivo_mon_25" name="efectivo_mon_25" value="<?php echo e($data['datos']['efectivo_mon_25'] ??"0"); ?>" placeholder="0" onkeyup='calcularMontoEfectivo()' min="0">
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                          <div class="form-group">
                              <label>Cantidad de MONEDAS CRC 50 </label>
                          <input type="number" class="form-control"  id="efectivo_mon_50" name="efectivo_mon_50" value="<?php echo e($data['datos']['efectivo_mon_50'] ??"0"); ?>" placeholder="0" onkeyup='calcularMontoEfectivo()' min="0">
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                          <div class="form-group">
                              <label>Cantidad de MONEDAS CRC 100 </label>
                          <input type="number" class="form-control"  id="efectivo_mon_100" name="efectivo_mon_100" value="<?php echo e($data['datos']['efectivo_mon_100'] ??"0"); ?>" placeholder="0" onkeyup='calcularMontoEfectivo()' min="0">
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                          <div class="form-group">
                              <label>Cantidad de MONEDAS CRC 500 </label>
                          <input type="number" class="form-control"  id="efectivo_mon_500" name="efectivo_mon_500" value="<?php echo e($data['datos']['efectivo_mon_500'] ??"0"); ?>" placeholder="0" onkeyup='calcularMontoEfectivo()' min="0">
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                          <div class="form-group">
                              <label>Cantidad de BILLETES CRC 1000 </label>
                          <input type="number" class="form-control"  id="efectivo_bill_1000" name="efectivo_bill_1000" value="<?php echo e($data['datos']['efectivo_bill_1000'] ??"0"); ?>" placeholder="0" onkeyup='calcularMontoEfectivo()' min="0">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                          <div class="form-group">
                              <label>Cantidad de BILLETES CRC 2000 </label>
                          <input type="number" class="form-control"  id="efectivo_bill_2000" name="efectivo_bill_2000" value="<?php echo e($data['datos']['efectivo_bill_2000'] ??"0"); ?>" placeholder="0" onkeyup='calcularMontoEfectivo()' min="0">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                          <div class="form-group">
                              <label>Cantidad de BILLETES CRC 5000 </label>
                          <input type="number" class="form-control"  id="efectivo_bill_5000" name="efectivo_bill_5000" value="<?php echo e($data['datos']['efectivo_bill_5000'] ??"0"); ?>" placeholder="0" onkeyup='calcularMontoEfectivo()' min="0">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                          <div class="form-group">
                              <label>Cantidad de BILLETES CRC 10,000 </label>
                          <input type="number" class="form-control"  id="efectivo_bill_10000" name="efectivo_bill_10000" value="<?php echo e($data['datos']['efectivo_bill_10000'] ??"0"); ?>" placeholder="0" onkeyup='calcularMontoEfectivo()' min="0">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                          <div class="form-group">
                              <label>Cantidad de BILLETES CRC 20,000 </label>
                          <input type="number" class="form-control"  id="efectivo_bill_20000" name="efectivo_bill_20000" value="<?php echo e($data['datos']['efectivo_bill_20000'] ??"0"); ?>" placeholder="0" onkeyup='calcularMontoEfectivo()' min="0">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                          <div class="form-group">
                              <label>Cantidad de BILLETES CRC 50,000 </label>
                          <input type="number" class="form-control"  id="efectivo_bill_50000" name="efectivo_bill_50000" value="<?php echo e($data['datos']['efectivo_bill_50000'] ??"0"); ?>" placeholder="0" onkeyup='calcularMontoEfectivo()' min="0">
                            </div>
                        </div>
                          <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                              <label>Terminar</label>
                              <input type="submit" class="btn btn-primary form-control" value="Cerrar Caja"/>
                            </div>

                          </div>
                      </form>
                        </div>
                      </div>
                      <!-- Gastos de cierre-->
                      <div class="col-12 col-md-12 col-lg-4" style="margin-top: 15px;">
                        <div class="card">
                          <div class="card-header">
                            <h4>Total de Caja</h4>
                            <div style="float: right;" class="input-group-btn">
                              <a  style="cursor: pointer; opacity:0.9;" href="<?php echo e(url('gastos/pendientes')); ?>"><i class="fas fa-cog"></i> Ver gastos de caja</a>
                            </div>
                          </div>
                          <div class="card-body">
                            <div class="text-white">
                              <div class="row" style="border-bottom: dotted 1px black;margin-top:15px;">
                                <div class="col-xs-4 col-md-4 col-lg-4">
                                  <input type="hidden" name="totalCaja" id="totalCaja"  value="0">
                                  <p class="font-20" style="font-size:15px;color: black; text-align: left;">Gastos</p>
                                </div>
                                <div class="col-xs-8 col-md-8 col-lg-8">
                                  <h4 class="font-20" style="color: black; text-align: right;" id="monto_gastos_lbl">CRC <strong> <?php echo e(number_format($data['total_gastos_caja'] ?? '0.00',2,".",",")); ?></strong></h4>
                                </div>

                              </div>
                              <div class="row" style="border-bottom: dotted 1px black; margin-top:15px;">
                                <div class="col-xs-4 col-md-4 col-lg-4">
                                  <p class="font-20" style="font-size:15px;color: black;  text-align: left;">Efectivo</p>
                                </div>
                                <div class="col-xs-8 col-md-8 col-lg-8">
                                  <h4 class="font-20" style="color: black; text-align: right;" id="monto_efectivo_lbl">CRC <strong> <?php echo e(number_format('0.00',2,".",",")); ?></strong></h4>
                                </div>

                              </div>
                              <div class="row" style="border-bottom: dotted 1px black; margin-top:15px;">
                                <div class="col-xs-4 col-md-4 col-lg-4">
                                  <p class="font-20" style="font-size:15px;color: black; text-align: left;">Tarjetas</p>
                                </div>
                                <div class="col-xs-8 col-md-8 col-lg-8">
                                  <h4 class="font-20" style="color: black; text-align: right;" id="monto_tarjetas_lbl">CRC <strong> <?php echo e(number_format('0.00',2,".",",")); ?></strong></h4>
                                </div>

                              </div>
                              <div class="row" style="border-bottom: dotted 1px black; margin-top:15px;">
                                <div class="col-xs-4 col-md-4 col-lg-4">
                                  <p class="font-20" style="font-size:15px;color: black;  text-align: left;">SINPE</p>
                                </div>
                                <div class="col-xs-8 col-md-8 col-lg-8">
                                  <h4 class="font-20" style="color: black;text-align: right;" id="monto_sinpe_lbl">CRC <strong> <?php echo e(number_format('0.00',2,".",",")); ?></strong></h4>
                                </div>

                              </div>

                              <div class="row" style="border-bottom: dotted 1px black; margin-top:15px;">
                                <div class="col-xs-4 col-md-4 col-lg-4">
                                  <p class="font-20" style="font-size:15px;color: black;  text-align: left; ">Sub Total</p>
                                </div>
                                <div class="col-xs-8 col-md-8 col-lg-8">
                                  <h4 class="font-20" style="color: black;text-align: right;" id="monto_subtotal_lbl">CRC <strong> <?php echo e(number_format('0.00',2,".",",")); ?></strong></h4>
                                </div>

                              </div>

                              <div class="row" style="border-bottom: dotted 1px black; margin-top:15px;">
                                <div class="col-xs-4 col-md-4 col-lg-4">
                                  <p class="font-20" style="font-size:15px;color: black; text-align: left;">Total</p>
                                </div>
                                <div class="col-xs-8 col-md-8 col-lg-8">
                                  <h4 class="font-20" style="color: black; text-align: right;" id="monto_total_lbl">CRC <strong> <?php echo e(number_format('0.00',2,".",",")); ?></strong></h4>
                                </div>

                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                        
                          
                    </div>   
                </div>      
                 
               
              </form>
            </div>
            </div>

    </div>
</div>
</section>
</div>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('script'); ?>

  <script src="<?php echo e(asset("assets/js/cierre_caja.js")); ?>"></script>
  

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/caja/cierre.blade.php ENDPATH**/ ?>