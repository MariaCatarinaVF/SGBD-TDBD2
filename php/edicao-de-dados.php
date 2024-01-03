<?php
require_once("custom/php/common.php");
//include'custom/css/ag.css';

if(isset($_REQUEST['origem']) != "")
{
    if ($_REQUEST['origem'] == "gestao-de-unidades")
    {
        if ($_REQUEST['estado'] == "apagar")
        {
            echo '<h3>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?</h3>';
            $id_unidade = $_GET['id'];
            $unidade = $_GET['tipo'];
            echo '<table>';
            echo '<tr>';
            echo '<th>id</th>';
            echo '<th>nome</th>';
            echo '</tr>';
            echo '<tr>';
            echo '<td>' . $id_unidade . '</td>';
            echo '<td>' . $unidade . '</td>';
            echo '</tr>';
            echo '</table>';

            echo '<form method="post" action="">';
            echo '<input type="hidden" name="estado" value="apagar_sucesso">';
            echo '<input type="submit" value="Apagar">';
            echo '</form>';

            voltarAtras();
        }
        else if ($_REQUEST['estado'] == "apagar_sucesso")
        {
            $id_unidade = $_REQUEST['id'];

            //início de transação
            mysqli_begin_transaction($link);

            //update do id da unidade nos subitens que estavam associados
            $query_definir_nulo = 'UPDATE subitem SET unit_type_id = NULL WHERE unit_type_id = ' . $id_unidade;
            $result_nulo = mysqli_query($link, $query_definir_nulo);

            if ($result_nulo == false)
            {
                // Rolback em caso de erro
                mysqli_rollback($result_nulo);
                echo '<p>Ocorreu algum erro ao definir nulo nos subitens: ' . mysqli_error($result_nulo) . '</p>';
            }
            else
            {
                //apaga a unidade
                $query_apagar_unidades = 'DELETE FROM subitem_unit_type WHERE subitem_unit_type.id = ' . $id_unidade;
                $result_unidades = mysqli_query($link, $query_apagar_unidades);

                if ($result_unidades == false)
                {
                    // Rolback em caso de erro
                    mysqli_rollback($result_unidades);
                    echo '<p>Ocorreu algum erro ao eliminar a unidade: ' . mysqli_error($result_unidades) . '</p>';
                }
                else
                {
                    // Confirmar transação
                    mysqli_commit($link);
                    echo '<h4>Eliminações realizadas com sucesso</h4>';
                    echo '<a href="/sgbd/gestao-de-unidades">Continuar</a>';
                }
            }
        }
        else if ($_REQUEST['estado'] == "editar")
        {
            $id_unidade = $_GET['id'];
            $unidade = $_GET['tipo'];

            echo '<form method="post" action="">';
            echo '<table>';
            echo '<tr>';
            echo '<th>id</th>';
            echo '<th>nome</th>';
            echo '</tr>';
            echo '<tr>';
            echo '<td>' . $id_unidade . '</td>';
            echo '<td><input type="text" name="novo_nome_para_unidade" value="' . $unidade . '"></td>';
            echo '</tr>';
            echo '</table>';
            echo "<input type='hidden' name='estado' value='confirmar'>";
            echo "<input type='submit' value='Submeter'>";
            echo '</form>';

            voltarAtras();
        }
        else if ($_REQUEST['estado'] == "confirmar")
        {
            $id_unidade = $_REQUEST['id'];
            $novo_nome_para_unidade = $_REQUEST['novo_nome_para_unidade'];
            $unidade = $_GET['tipo'];

            if($novo_nome_para_unidade == $unidade)
            {
                echo '<p>Não foi atualizado um nome para a unidade, pois o nome permanece o mesmo!</p>';
                voltarAtras();
            }
            else if(!preg_match("/^[a-zA-Z-Ç-ç\/á-úÁ-Úâ-ûÂ-Ûã-õÃ-Õä-üÄ-Ü]+$/", $novo_nome_para_unidade))
            {
                echo '<p>Deve incluir apenas letras, acentos e/ou "/"</p>';
                voltarAtras();
            }
            else
            {
                $query_para_editar_unidade = 'UPDATE subitem_unit_type SET name="' . $novo_nome_para_unidade . '" WHERE id="' . $id_unidade . '"';
                $conectarBD_query_para_editar_unidade = mysqli_query($link, $query_para_editar_unidade);

                if ($conectarBD_query_para_editar_unidade)
                {
                    echo '<h4>Atualizações realizadas com sucesso</h4>';
                    echo '<a href="/sgbd/gestao-de-unidades">Continuar</a>';
                }
                else
                {
                    echo '<p>Ocorreu algum erro ao editar a unidade ' . mysqli_error($conectarBD_query_para_editar_unidade) . '</p>';
                }
                echo '<br>';
                voltarAtras();
            }
        }
    }
    else if ($_REQUEST['origem'] == "gestao-de-valores-permitidos")
    {
        if ($_REQUEST['estado'] == "desativar")
        {
            $idValorPermitido = $_GET['id'];
            $value = $_GET['tipo'];

            $query_para_obter_valor_permitido = 'SELECT subitem_allowed_value.subitem_id, subitem_allowed_value.state, subitem_allowed_value.value
                                          FROM subitem_allowed_value,subitem
                                          WHERE subitem_allowed_value.subitem_id=subitem.id AND subitem_allowed_value.id='.$idValorPermitido.'';
            $conectar_query_para_obter_valor_permitido = mysqli_query($link,$query_para_obter_valor_permitido);
            $devolve_numero_linhas_valor_permitido = mysqli_num_rows($conectar_query_para_obter_valor_permitido);

                if ($imprime_subitens = $conectar_query_para_obter_valor_permitido->fetch_assoc())
                {
                    $idSubitem = $imprime_subitens['subitem_id'];
                    $estado = $imprime_subitens['state'];

                    echo '<h4>Pretende desativar o item?</h4>';
                    echo '<form method="post" action="">';
                    echo '<table>';
                    echo '<tr>';
                    echo '<th>id</th>';
                    echo '<th>subitem_id</th>';
                    echo '<th>value</th>';
                    echo '<th>state</th>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>' . $idValorPermitido . '</td>';
                    echo '<td>' . $idSubitem . '</td>';
                    echo '<td>' . $value . '</td>';
                    echo '<td>' . $estado . '</td>';
                    echo '</tr>';
                    echo '</table>';
                    echo "<input type='hidden' name='estado' value='desativar_sucesso'>";
                    echo "<input type='submit' value='Submeter'>";
                    echo '</form>';
                    echo '<br>';
                    voltarAtras();
                }
        }
        else if($_REQUEST['estado'] == "desativar_sucesso")
        {
            $idValorPermitido = $_REQUEST['id'];

            $query_para_desativar = 'UPDATE subitem_allowed_value SET state="inactive" 
                                     WHERE id='.$idValorPermitido.'';
            $conectar_query_para_desativar = mysqli_query($link,$query_para_desativar);

            if ($conectar_query_para_desativar)
            {
                echo '<h4>Atualizações realizadas com sucesso</h4>';
                echo '<a href="/sgbd/gestao-de-valores-permitidos">Continuar</a>';
            }
            else
            {
                echo '<p>Ocorreu algum erro ao desativar o valor permitido '.mysqli_error($conectar_query_para_desativar).'</p>';
            }
        }

        if ($_REQUEST['estado'] == "ativar")
        {
            $idValorPermitido = $_GET['id'];
            $value = $_GET['tipo'];

            $query_para_obter_valor_permitido = 'SELECT subitem_allowed_value.subitem_id, subitem_allowed_value.state, subitem_allowed_value.value
                                          FROM subitem_allowed_value,subitem
                                          WHERE subitem_allowed_value.subitem_id=subitem.id AND subitem_allowed_value.id='.$idValorPermitido.'';
            $conectar_query_para_obter_valor_permitido = mysqli_query($link,$query_para_obter_valor_permitido);
            $devolve_numero_linhas_valor_permitido = mysqli_num_rows($conectar_query_para_obter_valor_permitido);

            if ($imprime_subitens = $conectar_query_para_obter_valor_permitido->fetch_assoc())
            {
                $idSubitem = $imprime_subitens['subitem_id'];
                $estado = $imprime_subitens['state'];

                echo '<h4>Pretende ativar o item?</h4>';
                echo '<form method="post" action="">';
                echo '<table>';
                echo '<tr>';
                echo '<th>id</th>';
                echo '<th>subitem_id</th>';
                echo '<th>value</th>';
                echo '<th>state</th>';
                echo '</tr>';
                echo '<tr>';
                echo '<td>' . $idValorPermitido . '</td>';
                echo '<td>' . $idSubitem . '</td>';
                echo '<td>' . $value . '</td>';
                echo '<td>' . $estado . '</td>';
                echo '</tr>';
                echo '</table>';
                echo "<input type='hidden' name='estado' value='ativar_sucesso'>";
                echo "<input type='submit' value='Submeter'>";
                echo '</form>';
                echo '<br>';
                voltarAtras();
            }
        }
        else if($_REQUEST['estado'] == "ativar_sucesso")
        {
            $idValorPermitido = $_REQUEST['id'];

            $query_para_ativar = 'UPDATE subitem_allowed_value SET state="active" 
                                     WHERE id='.$idValorPermitido.'';
            $conectar_query_para_ativar = mysqli_query($link,$query_para_ativar);

            if ($conectar_query_para_ativar)
            {
                echo '<h4>Atualizações realizadas com sucesso</h4>';
                echo '<a href="/sgbd/gestao-de-valores-permitidos">Continuar</a>';
            }
            else
            {
                echo '<p>Ocorreu algum erro ao ativar o valor permitido '.mysqli_error($conectar_query_para_ativar).'</p>';
            }
        }

        else if ($_REQUEST['estado'] == "editar")
        {
            $idValorPermitido = $_GET['id'];
            $value = $_GET['tipo'];

            $query_para_obter_valor_permitido = 'SELECT subitem_allowed_value.subitem_id, subitem_allowed_value.state, subitem_allowed_value.value
                                          FROM subitem_allowed_value,subitem
                                          WHERE subitem_allowed_value.subitem_id=subitem.id AND subitem_allowed_value.id='.$idValorPermitido.'';
            $conectar_query_para_obter_valor_permitido = mysqli_query($link,$query_para_obter_valor_permitido);
            $devolve_numero_linhas_valor_permitido = mysqli_num_rows($conectar_query_para_obter_valor_permitido);

            if ($imprime_subitens = $conectar_query_para_obter_valor_permitido->fetch_assoc())
            {
                $idSubitem = $imprime_subitens['subitem_id'];
                $estado = $imprime_subitens['state'];

                echo '<form method="post" action="">';
                echo '<table>';
                echo '<tr>';
                echo '<th>id</th>';
                echo '<th>subitem_id</th>';
                echo '<th>value</th>';
                echo '<th>state</th>';
                echo '</tr>';
                echo '<tr>';
                echo '<td>' . $idValorPermitido . '</td>';
                echo '<td>';
                echo '<input type="hidden" name="id_subitem_antigo" value = "'.$idSubitem.'">';
                echo '<select name="idSubitemNovo">';
                echo '<option name="idSubitemNovo" value="'.$idSubitem.'">'.$idSubitem.'</option>';
                $query_obter_subitens = 'SELECT id, value_type 
                                         FROM subitem WHERE value_type="enum" AND subitem.id
					                     NOT IN (SELECT id 
                                                 FROM subitem 
                                                 WHERE id='.$idSubitem.'); ';
                $conectar_query_para_obter_subitens = mysqli_query($link, $query_obter_subitens);

                while ($subitens = $conectar_query_para_obter_subitens->fetch_assoc())
                {
                    echo '<option name="idSubitemNovo" >' . $subitens['id'] . '</option>';
                }
                echo '</select>';
                echo'</td>';
                echo '<td> <input type="text" name="tipoNovo" value="' . $value . '"> </td>';
                echo '<td>' . $estado . '</td>';
                echo '</tr>';
                echo '</table>';
                echo "<input type='hidden' name='estado' value='editar_valor'>";
                echo "<input type='submit' value='Submeter'>";
                echo '<br>';
                voltarAtras();
            }
        }
        else if($_REQUEST['estado'] == "editar_valor")
        {
            $idSubitem_Antigo = $_REQUEST['id_subitem_antigo'];
            $value_Antigo = $_GET['tipo'];

            $valueNovo = $_REQUEST['tipoNovo'];
            $idSubitem_Novo = $_REQUEST['idSubitemNovo'];
            $idValorPermitido = $_REQUEST['id'];

            if($idSubitem_Antigo == $idSubitem_Novo && $value_Antigo == $valueNovo)
            {
                echo '<p>Não foi atualizado um id novo para o subitem nem um nome novo para o valor permitido!</p>';
                voltarAtras();
            }
            else if(!preg_match("/^[a-zA-Z-Ç-ç\/á-úÁ-Úâ-ûÂ-Ûã-õÃ-Õä-üÄ-Ü]+$/", $valueNovo))
            {
                echo '<p>Deve incluir apenas letras, acentos e/ou "/"</p>';
                voltarAtras();
            }
            else
            {
                if($valueNovo && $idSubitem_Novo)
                {
                    $query_para_eliminar_valor = 'DELETE FROM subitem_allowed_value WHERE id = '.$idValorPermitido.'';
                    $conectar_query_para_eliminar_valor = mysqli_query($link, $query_para_eliminar_valor);

                    if($conectar_query_para_eliminar_valor == false)
                    {
                        echo '<p>Ocorreu algum problema ao eliminar o valor permitido</p>';
                    }
                    else
                    {
                        $query_inserir_valor = "INSERT INTO subitem_allowed_value(id,subitem_id,value,state) VALUES ('$idValorPermitido','$idSubitem_Novo','$valueNovo','active')";
                        $conectar_query_inserir_valor = mysqli_query($link, $query_inserir_valor);

                        if($conectar_query_inserir_valor == false)
                        {
                            echo '<p>Ocorreu algum problema ao inserir o valor permitido</p>';
                        }
                        else
                        {
                            echo '<h4>Atualizações realizadas com sucesso</h4>';
                            echo '<a href="/sgbd/gestao-de-valores-permitidos">Continuar</a>';
                        }
                    }
                }
                else if($valueNovo)
                {
                    $query_editar_valor = 'UPDATE subitem_allowed_value SET value = "'.$valueNovo.'" WHERE subitem_allowed_value.id='.$idValorPermitido.';';
                    $conectar_query_para_editar_valor = mysqli_query($link, $query_editar_valor);

                    if ($conectar_query_para_editar_valor)
                    {
                        echo '<h4>Atualizações realizadas com sucesso</h4>';
                        echo '<a href="/sgbd/gestao-de-valores-permitidos">Continuar</a>';
                    }
                    else
                    {
                        echo '<p>Ocorreu algum erro ao editar o valor permitido ' . mysqli_error($conectar_query_para_editar_valor) . '</p>';
                    }
                }
                else if($idSubitem_Novo)
                {
                    $query_editar_subitem_id='UPDATE subitem_allowed_value SET subitem_id = '.$idSubitem_Novo.' WHERE subitem_id='.$idSubitem.'';
                    $conectar_query_editar_subitem_id=mysqli_query($link,$query_editar_subitem_id);

                    if($conectar_query_editar_subitem_id)
                    {
                        echo '<h4>Atualizações realizadas com sucesso</h4>';
                        echo '<a href="/sgbd/gestao-de-valores-permitidos">Continuar</a>';
                    }
                    else
                    {
                        echo '<p>Ocorreu algum erro ao editar o valor permitido ' . mysqli_error($conectar_query_editar_subitem_id) . '</p>';
                    }
                }
                else
                {
                    echo '<p>Não foi editado nada</p>';
                }
                echo '<br>';
                voltarAtras();
            }
        }
        else if ($_REQUEST['estado'] == "apagar")
        {
            echo '<h3>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?</h3>';
            $idValorPermitido = $_GET['id'];
            $value = $_GET['tipo'];

            $query_para_obter_valor_permitido = 'SELECT subitem_allowed_value.subitem_id, subitem_allowed_value.state, subitem_allowed_value.value
                                          FROM subitem_allowed_value,subitem
                                          WHERE subitem_allowed_value.subitem_id=subitem.id AND subitem_allowed_value.id='.$idValorPermitido.'';
            $conectar_query_para_obter_valor_permitido = mysqli_query($link,$query_para_obter_valor_permitido);
            $devolve_numero_linhas_valor_permitido = mysqli_num_rows($conectar_query_para_obter_valor_permitido);

            if ($imprime_subitens = $conectar_query_para_obter_valor_permitido->fetch_assoc())
            {
                $idSubitem = $imprime_subitens['subitem_id'];
                $estado = $imprime_subitens['state'];

                echo '<form method="post" action="">';
                echo '<table>';
                echo '<tr>';
                echo '<th>id</th>';
                echo '<th>subitem_id</th>';
                echo '<th>value</th>';
                echo '<th>state</th>';
                echo '</tr>';
                echo '<tr>';
                echo '<td>' . $idValorPermitido . '</td>';
                echo '<td>' . $idSubitem . '</td>';
                echo '<td>' . $value . '</td>';
                echo '<td>' . $estado . '</td>';
                echo '</tr>';
                echo '</table>';
                echo "<input type='hidden' name='estado' value='apagar_valor'>";
                echo "<input type='submit' value='Submeter'>";
                echo '</form>';
                echo '<br>';
                voltarAtras();
            }
        }
        else if($_REQUEST['estado'] == "apagar_valor")
        {
            $idValorPermitido = $_REQUEST['id'];
            $query_para_eliminar_valor_permitido = 'DELETE FROM subitem_allowed_value WHERE id= ' .$idValorPermitido.'';
            $conectar_query_para_eliminar_valor_permitido = mysqli_query($link,$query_para_eliminar_valor_permitido);

            if($conectar_query_para_eliminar_valor_permitido)
            {
                echo '<h4>Eliminações realizadas com sucesso</h4>';
                echo '<a href="/sgbd/gestao-de-valores-permitidos">Continuar</a>';
            }
            else
            {
                echo '<p>Ocorreu algum erro ao eliminar o valor permitido '.mysqli_error($conectar_query_para_eliminar_valor_permitido).'</p>';
            }
        }
    }
    else
    {
        echo '<p>Não foi selecionado nada para editar! Vá a uma das outras páginas e clique em editar, desativar e/ou apagar.</p>';
    }
}
else
{
    echo '<p>Não tem página de origem.</p>';
}
?>