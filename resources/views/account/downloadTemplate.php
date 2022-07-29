<html>
    <body>
        <table>
            <thead>
                <tr>
                <?php foreach($headers as $cell){
                    echo "<th>".$cell."</th>";
                } ?>
                </tr>
            </thead>
             <tbody>                
                <?php foreach($data as $row){
                    echo "<tr>";
                        foreach($headers as $key=>$value){
                            if(isset($row[$key]))
                            echo "<td>".$row[$key]."</td>";
                            else
                            echo "<td></td>"; 
                        }
                    echo "</tr>";
                } ?>
            </tbody>
        </table>
    </body>
</html>