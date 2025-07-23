<?php
require_once 'conexao.php';

/**
 * Cria a tabela de ciclos de metas se não existir
 */
function criarTabelaCiclosMetas() {
    try {
        $conn = getConexao();
        
        $sql = "CREATE TABLE IF NOT EXISTS ciclos_metas (
            id INT(11) NOT NULL AUTO_INCREMENT,
            salao_id INT(11) NOT NULL,
            data_inicio DATE NOT NULL,
            data_fim DATE NOT NULL,
            agendamentos_confirmados INT(11) DEFAULT 0,
            meta_50_atingida BOOLEAN DEFAULT FALSE,
            meta_100_atingida BOOLEAN DEFAULT FALSE,
            bonus_50_pago BOOLEAN DEFAULT FALSE,
            bonus_100_pago BOOLEAN DEFAULT FALSE,
            valor_bonus_pago DECIMAL(10,2) DEFAULT 0.00,
            ativo BOOLEAN DEFAULT TRUE,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (salao_id) REFERENCES saloes(id) ON DELETE CASCADE,
            INDEX idx_salao_ativo (salao_id, ativo),
            INDEX idx_periodo (data_inicio, data_fim)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $conn->exec($sql);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao criar tabela ciclos_metas: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém o ciclo ativo atual para um salão
 */
function obterCicloAtivo($salao_id) {
    try {
        $conn = getConexao();
        
        $stmt = $conn->prepare("
            SELECT * FROM ciclos_metas 
            WHERE salao_id = ? AND ativo = TRUE 
            AND CURDATE() BETWEEN data_inicio AND data_fim
            ORDER BY data_inicio DESC 
            LIMIT 1
        ");
        
        $stmt->execute([$salao_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erro ao obter ciclo ativo: " . $e->getMessage());
        return false;
    }
}

/**
 * Cria um novo ciclo de metas para um salão
 */
function criarNovoCiclo($salao_id, $data_inicio = null) {
    try {
        $conn = getConexao();
        
        // Se não foi fornecida data de início, usa a data atual
        if (!$data_inicio) {
            $data_inicio = date('Y-m-d');
        }
        
        // Calcula a data fim (30 dias após o início)
        $data_fim = date('Y-m-d', strtotime($data_inicio . ' + 29 days'));
        
        // Desativa ciclos anteriores
        $stmt = $conn->prepare("UPDATE ciclos_metas SET ativo = FALSE WHERE salao_id = ? AND ativo = TRUE");
        $stmt->execute([$salao_id]);
        
        // Cria o novo ciclo
        $stmt = $conn->prepare("
            INSERT INTO ciclos_metas (salao_id, data_inicio, data_fim, ativo) 
            VALUES (?, ?, ?, TRUE)
        ");
        
        $stmt->execute([$salao_id, $data_inicio, $data_fim]);
        
        return $conn->lastInsertId();
    } catch (Exception $e) {
        error_log("Erro ao criar novo ciclo: " . $e->getMessage());
        return false;
    }
}

/**
 * Atualiza a contagem de agendamentos confirmados para um ciclo
 */
function atualizarContagemAgendamentos($salao_id) {
    try {
        $conn = getConexao();
        
        // Obtém o ciclo ativo
        $ciclo = obterCicloAtivo($salao_id);
        if (!$ciclo) {
            // Se não há ciclo ativo, cria um novo
            $ciclo_id = criarNovoCiclo($salao_id);
            if (!$ciclo_id) return false;
            
            $ciclo = obterCicloAtivo($salao_id);
        }
        
        // Conta agendamentos confirmados e realizados (pagos) no período do ciclo
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total 
            FROM agendamentos 
            WHERE salao_id = ? 
            AND (status = 'confirmado' OR status = 'realizado') 
            AND data BETWEEN ? AND ?
        ");
        
        $stmt->execute([$salao_id, $ciclo['data_inicio'], $ciclo['data_fim']]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_confirmados = $resultado['total'];
        
        // Verifica se as metas foram atingidas (converte para inteiro)
        $meta_50_atingida = $total_confirmados >= 50 ? 1 : 0;
        $meta_100_atingida = $total_confirmados >= 100 ? 1 : 0;
        
        // Atualiza o ciclo
        $stmt = $conn->prepare("
            UPDATE ciclos_metas 
            SET agendamentos_confirmados = ?,
                meta_50_atingida = ?,
                meta_100_atingida = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $total_confirmados,
            $meta_50_atingida,
            $meta_100_atingida,
            $ciclo['id']
        ]);
        
        return [
            'ciclo_id' => $ciclo['id'],
            'agendamentos_confirmados' => $total_confirmados,
            'meta_50_atingida' => $meta_50_atingida == 1,
            'meta_100_atingida' => $meta_100_atingida == 1,
            'data_inicio' => $ciclo['data_inicio'],
            'data_fim' => $ciclo['data_fim'],
            'dias_restantes' => max(0, (strtotime($ciclo['data_fim']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24) + 1)
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao atualizar contagem: " . $e->getMessage());
        return false;
    }
}

/**
 * Marca o bônus como pago para um ciclo
 */
function marcarBonusPago($ciclo_id, $valor_bonus, $tipo_meta) {
    try {
        $conn = getConexao();
        
        $campo_bonus = $tipo_meta == 50 ? 'bonus_50_pago' : 'bonus_100_pago';
        
        $stmt = $conn->prepare("
            UPDATE ciclos_metas 
            SET {$campo_bonus} = TRUE,
                valor_bonus_pago = valor_bonus_pago + ?
            WHERE id = ?
        ");
        
        $stmt->execute([$valor_bonus, $ciclo_id]);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao marcar bônus como pago: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém o histórico de ciclos de um salão
 */
function obterHistoricoCiclos($salao_id, $limite = 6) {
    try {
        $conn = getConexao();
        
        $stmt = $conn->prepare("
            SELECT 
                id,
                data_inicio,
                data_fim,
                agendamentos_confirmados,
                meta_50_atingida,
                meta_100_atingida,
                valor_bonus_pago,
                DATE_FORMAT(data_inicio, '%d/%m/%Y') as periodo_formatado
            FROM ciclos_metas 
            WHERE salao_id = ? 
            AND ativo = FALSE
            ORDER BY data_inicio DESC 
            LIMIT ?
        ");
        
        $stmt->execute([$salao_id, $limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erro ao obter histórico: " . $e->getMessage());
        return [];
    }
}

/**
 * Verifica e finaliza ciclos expirados
 */
function finalizarCiclosExpirados() {
    try {
        $conn = getConexao();
        
        // Busca ciclos ativos que já expiraram
        $stmt = $conn->prepare("
            SELECT id, salao_id FROM ciclos_metas 
            WHERE ativo = TRUE AND data_fim < CURDATE()
        ");
        
        $stmt->execute();
        $ciclos_expirados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($ciclos_expirados as $ciclo) {
            // Finaliza o ciclo expirado
            $stmt = $conn->prepare("UPDATE ciclos_metas SET ativo = FALSE WHERE id = ?");
            $stmt->execute([$ciclo['id']]);
            
            // Cria um novo ciclo para o salão
            criarNovoCiclo($ciclo['salao_id']);
        }
        
        return count($ciclos_expirados);
    } catch (Exception $e) {
        error_log("Erro ao finalizar ciclos expirados: " . $e->getMessage());
        return false;
    }
}

// Inicializa a tabela se necessário
criarTabelaCiclosMetas();
?>