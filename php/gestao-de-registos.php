<?php
require_once("custom/php/common.php");
if (is_user_logged_in() && current_user_can("manage_records")) 
{
    $name = '';
    $birth = '';
    $nameTutor = '';
    $phone = '';
    $email = '';
    if ($_REQUEST['estado'] == '')
    {
        $_REQUEST['estado'] = 'inicio';
    }
    if ($_REQUEST['estado'] == 'inicio') 
    {
        echo "<table style='width:100%'>";
        echo '<thead>';
        echo "<tr>
                <th>Nome</th>
                <th>Data de nascimento</th>
                <th>Enc. de educação</th>
                <th>Telefone do Enc.</th>
                <th>E-mail</th>
                <th>Ação</th>
                <th>Registos</th>
            </tr>";
        echo '</thead>';
        $children = "SELECT id, name, birth_date, tutor_name, tutor_phone, tutor_email 
                    FROM child
                    ORDER BY name ASC";
$results = mysqli_query($link, $children);

if (mysqli_num_rows($results) == 0) 
{
    echo "Não há crianças";
} 
else 
{
    echo '<tbody>';
    while ($result = mysqli_fetch_assoc($results)) 
    {
        echo '<tr>';
        echo '<td>' . $result['name'] . '</td>';
        echo '<td>' . $result['birth_date'] . '</td>';
        echo '<td>' . $result['tutor_name'] . '</td>';
        echo '<td>' . $result['tutor_phone'] . '</td>';
        echo '<td>';
        echo '<a href=edicao-de-dados>[editar]</a><a href=#>[apagar]</a>';
        echo '</td>';
        echo $result['tutor_email'] ? '<td>' . $result['tutor_email'] . '</td>' : '<td>' . 'Sem email disponível' . '</td>';

        // Fetch and display items for each child
        $registros = "SELECT UPPER(item.name) AS item_name, item.id, subitem.id as idsub, item.id as itemID
                        FROM item, subitem, `value` as valor
                        WHERE " . $result['id'] . " = valor.child_id
                            AND valor.subitem_id = subitem.id
                            AND subitem.item_id = item.id
                            GROUP BY item.id
                            ORDER BY item_name";
        $result_regi = mysqli_query($link, $registros);

        $everything = '';
        echo '<td>';
        if (!$result_regi) 
        {
            die('Erro na query: ' . mysqli_error($link));
        } 
        else 
        {
            while ($Regi = mysqli_fetch_assoc($result_regi)) 
            {
                if($everything != '')
                {
                    $everything .='<br>';
                }

                $everything .=$Regi['item_name'] . ': ';

                $time_query = "SELECT valor.date AS datas, valor.producer AS producer
                                FROM `value` AS valor, subitem
                                WHERE valor.subitem_id = subitem.id
                                AND subitem.item_id = " . $Regi['itemID'] . "
                                GROUP BY datas
                                ORDER BY datas";
                $time = mysqli_query($link, $time_query);

                if (!$time) 
                {
                    die('Erro na query: ' . mysqli_error($link));
                } 
                else 
                {
                    while ($Time = mysqli_fetch_assoc($time)) 
                    {
                        $everything .= "<br><a href=edicao-de-dados>[editar]</a><a href=>[apagar]</a> <strong> " . 
                        $Time['datas'] . "</strong> (" . $Time['producer'] . ") - ";

                        $valores = "SELECT subitem.name, valor.`value` AS valores, subitem.form_field_order
                                        FROM subitem, `value` as valor
                                        WHERE valor.subitem_id = " . $Regi['idsub'] . "
                                        AND subitem.id = valor.subitem_id
                                        AND valor.date = '" . $Time['datas'] . "'
                                        ORDER BY subitem.form_field_order;";
                        $resu_valores = mysqli_query($link, $valores);

                        if (!$resu_valores) 
                        {
                            die('Erro na query: ' . mysqli_error($link));
                        } 
                        else 
                        {
                            while ($Values = mysqli_fetch_assoc($resu_valores)) 
                            {
                                $everything .= $Values['name'] . ' (' . $Values['valores'] . '); ';
                            }
                        }
                    }
                }    
            }
        }
        echo $everything;

        echo '</td>';

        echo '</tr>';
    }
    echo '</tbody>';
}
        echo "</table>";

        echo '<div class="formulario_registo">';
        echo '<strong>Dados de registro - introdução</strong>';
        echo '<br>';
        echo '<br>';
        echo '<strong class="vermelho">Obrigatorio *</strong>';
        echo "<form method='post' action=>";
        echo '<strong>Nome completo:</strong><strong class="vermelho">*</strong>';
        echo "<input type='text' id='name2' name='name2'>";
        echo '<strong>Data de nascimento (AAAA-MM-DD):</strong><strong class="vermelho">*</strong>';
        echo "<input type='text' id='birth' name='birth'>";
        echo '<strong>Nome completo do encarregado de educação:</strong><strong class="vermelho">*</strong>';
        echo "<input type='text' id='nameTutor' name='nameTutor'>";
        echo '<strong>Telefone do encarregado de educação (9 digitos):</strong><strong class="vermelho">*</strong>';
        echo "<input type='text' id='phone' name='phone'>";
        echo "<strong>Edereço de e-mail do tutor:</strong>";
        echo "<input type='text' id='email' name='email'>";
        echo "<input type='hidden' name='estado' value='validar'/>";
        echo "<input type='submit' name='submeter' value='SUBMETER'/>";
        echo "</form>";
        echo '</div>';

    }
    else if ($_REQUEST['estado'] == 'validar') 
    {

        $errors = false;
        $name = htmlspecialchars($_REQUEST['name2']);
        $birth = htmlspecialchars($_REQUEST['birth']);
        $nameTutor = htmlspecialchars($_REQUEST['nameTutor']);
        $phone = htmlspecialchars($_REQUEST['phone']);
        $email = htmlspecialchars($_REQUEST['email']);


        if (empty($name)) 
        {
            echo "É necessario inserir um nome.<br>";
            $errors = true;
        }
        if(!empty($name) && !preg_match("/^[\p{L}\s]*$/u", $name))
        {
            echo "O nome contém caracteres inválidos.<br>";
            $errors =true;
        }
        if (empty($birth)) 
        {
            echo "É necessario inserir uma data.<br>";
            $errors = true;
        }
        if (!empty($birth) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $birth)) 
        {
            echo "A data deve ser do formato AAAA-MM-DD.<br>";
            $errors = true;
        }
        if (empty($nameTutor)) 
        {
            echo "É necessário inserir o nome do encarregado de educação.<br>";
            $errors = true;
        }
        if(!empty($nameTutor) && !preg_match("/^[\p{L}\s]*$/u", $nameTutor))
        {
            echo "O nome do encarregado de educação contém caracteres inválidos.<br>";
            $errors =true;
        }
        if (empty($phone)) 
        {
            echo "É necessário inserir o número de telemóvel.<br>";
            $errors = true;
        }
        if (!empty($phone) && !preg_match("/^\d{9}$/", $phone)) 
        {
            echo "O número de telemóvel deve conter 9 números.<br>";
            $errors = true;
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) 
        {
            echo "O email contém o formato errado.<br>";
            $errors = true;
        }

        if (!$errors) 
        {
            echo '<h3>Dados de registro - validação</h3>';
            echo '<p>Estamos prestes a inserir os dados abaixo na base de dados.</p>';
            echo '<p>Confirma que os dados estão corretos e pretende submeter os mesmos?</p>';
            echo '<p><strong>Nome: </strong>' . $name . '<strong> Data de nascimento: </strong>' . $birth . '<strong> Enc. de educação: </strong>' 
            . $nameTutor . '<strong> Telefone do Enc: </strong>' . $phone . '<strong> e-mail: </strong>' . $email;
            echo '<form method="post" action="">';
            echo '<input type="hidden" name="name2" value="' . $name . '">';
            echo '<input type="hidden" name="birth" value="' . $birth . '">';
            echo '<input type="hidden" name="nameTutor" value="' . $nameTutor . '">';
            echo '<input type="hidden" name="phone" value="' . $phone . '">';
            echo '<input type="hidden" name="email" value="' . $email . '">';
            echo '<input type="hidden" name="estado" value="inserir">';
            echo '<input type="submit" value="inserir">';
            echo '</form>';
            }
        echo '<br>';
        echo '<a href="">Voltar atrás</a>';
    } 
    else if ($_REQUEST['estado'] == 'inserir') 
    {

        $name = htmlspecialchars($_REQUEST['name2']);
        $birth = htmlspecialchars($_REQUEST['birth']);
        $nameTutor = htmlspecialchars($_REQUEST['nameTutor']);
        $phone = htmlspecialchars($_REQUEST['phone']);
        $email = htmlspecialchars($_REQUEST['email']);

        $gestao_registros=paginaAtual();
        $id = "SELECT MAX(id) AS max_id FROM child";
        $resultID = mysqli_query($link, $id);
        $row = mysqli_fetch_assoc($resultID);
        $lastID = $row['max_id'] + 1;
        $insert = "INSERT INTO `child` (`id`, `name`, `birth_date`, `tutor_name`, `tutor_phone`, `tutor_email`) 
          VALUES ('" . $lastID . "', '" . $name . "', '" . $birth . "', '" . $nameTutor . "', '" . $phone . "', '" . $email . "');";
        print_r($insert);
        if (mysqli_query($link, $insert)) 
        {
            echo '<h3>Dados de registro - inserção</h3>';
            echo '<p>Os dados foram inseridos com sucesso.</p>';
            echo '<h3>Inserio os dados</h3>';
            echo "Inserio os seguintes dados:<br>";
            echo "Nome: " . $name . "<br>";
            echo "Data de nascimento: " . $birth . "<br>";
            echo "Enc. de educação: " . $nameTutor . "<br>";
            echo "Telefone do Enc.: " . $phone . "<br>";
            echo "e-mail: " . $email . "<br>";
            echo '<h3>Inseriu os dados de registro com sucesso.</h3>';
            echo '<h3>Clique em Continuar para avançar.</h3>';
        } 
        else 
        {
            echo "Ouve um erro ao inserir os dados na base de dados";
        }
        echo '<form method="post" action="">';
        echo '<input type="hidden" name="estado" value="inicio">';
        echo '<input type="submit" value="CONTINUAR">';
        echo '</form>';
    }
}
else 
{
    echo "Não tem autorização para aceder a esta página";
}
include'custom/css/ag.css';
?>