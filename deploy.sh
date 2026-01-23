#!/bin/bash

# Script de Deploy para Produção - InfoVISA
# Uso: bash deploy.sh

echo "=========================================="
echo "  Deploy InfoVISA - Sistema de Pactuação"
echo "=========================================="
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Diretório do projeto
PROJECT_DIR="/var/www/html/infovisacore"

echo -e "${YELLOW}1. Navegando para o diretório do projeto...${NC}"
cd $PROJECT_DIR || exit

echo -e "${YELLOW}2. Fazendo backup do banco de dados...${NC}"
BACKUP_FILE="backup_antes_deploy_$(date +%Y%m%d_%H%M%S).sql"
pg_dump -U postgres -d infovisa > $BACKUP_FILE
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Backup criado: $BACKUP_FILE${NC}"
else
    echo -e "${RED}✗ Erro ao criar backup!${NC}"
    exit 1
fi

echo -e "${YELLOW}3. Fazendo pull das alterações do Git...${NC}"
git pull origin main
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Código atualizado${NC}"
else
    echo -e "${RED}✗ Erro ao fazer pull!${NC}"
    exit 1
fi

echo -e "${YELLOW}4. Limpando caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
echo -e "${GREEN}✓ Caches limpos${NC}"

echo -e "${YELLOW}5. Ajustando permissões...${NC}"
sudo chown -R apache:apache storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}✓ Permissões ajustadas${NC}"

echo -e "${YELLOW}6. Reiniciando serviços...${NC}"
sudo systemctl restart php-fpm
sudo systemctl restart httpd
echo -e "${GREEN}✓ Serviços reiniciados${NC}"

echo ""
echo -e "${GREEN}=========================================="
echo "  Deploy concluído com sucesso!"
echo "==========================================${NC}"
echo ""
echo "Próximos passos:"
echo "1. Acesse: https://sistemas.saude.to.gov.br/infovisacore/admin/configuracoes/pactuacao"
echo "2. Teste as funcionalidades"
echo "3. Se houver problemas, restaure o backup: $BACKUP_FILE"
echo ""
