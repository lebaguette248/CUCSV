<div class="header_CUCSV"><h1>CUCSV Admin Page</h1>
    <p>Welcome to the CUCSV plugin admin page.</p>
</div>

<div class="content_CUCSV">
    <p>CSV has to have atleast</p>
    <ul>
        <li>email</li>
        <li>username</li>
        <li>password</li>
        <li>permissions</li>
    </ul>
    <p>These need to be in the CSV Format " email; username; password; rank " with Semicolon Line Separators</p>
    <a class="TestCSV_CUCSV" href="<?php echo plugins_url('CUCSV/assets/Test.csv'); ?>" draggable="false"
       download="Test.csv">Download
        testCSV</a>
    <div class="form_CUCSV">
        <p>Please enter a CSV</p>
        <form method="post" enctype="multipart/form-data">
            <label for="csv_file">Choose File</label>
            <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
            <input type="submit" id="submit_CUCSV" disabled value="Upload CSV">
        </form>
    </div>
    <a class="OutputCSV_CUCSV" id="outputCSV"
       draggable="false" download="output.csv"
       href="<?php echo plugins_url('CUCSV/assets/output.csv'); ?>">
        Download Output CSV</a>
    <div>
        <p> Error Log</p>
        <div class="error_log">
        </div>
    </div>
</div>


<script>
    document.getElementById('csv_file').addEventListener('change', makeEnable);

    function makeEnable() {
        document.getElementById('submit_CUCSV').disabled = false;
        console.log("DONE");
    }
</script>