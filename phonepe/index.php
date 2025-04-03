<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="style.css">
   <title>Document</title>
</head>

<body>

   <div class="container">
      <div class="text">
         Contact us Form
      </div>
      <form action="post.php" method="post">
         <div class="form-row">
            <div class="input-data">
               <input type="text" name="size" required>
               <div class="underline"></div>
               <label for="">size</label>
            </div>
            <div class="input-data">
               <input type="text" name="merchantId" value="PURELODQRUAT" required>
               <div class="underline"></div>
               <label for="">merchantId</label>
            </div>
         </div>
         <div class="form-row">
            <div class="input-data">
               <input type="text" name="storeId" value="teststore1" required>
               <div class="underline"></div>
               <label for="">storeId</label>
            </div>
            <div class="input-data">
               <input type="text" name="qrCodeId" value="QR2403081640558693393018" required>
               <div class="underline"></div>
               <label for="">qrCodeId</label>
            </div>
         </div>
         <div class="form-row">
            <div class="input-data textarea">
               <!-- <textarea rows="8" cols="80" required></textarea> -->
               
               <div class="form-row submit-btn">
                  <div class="input-data">
                     <div class="inner"></div>
                     <input type="submit" value="submit">
                  </div>
               </div>
      </form>
   </div>

</body>

</html>