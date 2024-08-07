from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

# Configurações do Chrome
chrome_options = Options()
chrome_options.add_argument("--headless")  # Remova este comentário se não precisar do modo headless

# Cria o serviço para o ChromeDriver usando o webdriver-manager
service = Service(ChromeDriverManager().install())

# Configurar o WebDriver
driver = webdriver.Chrome(service=service, options=chrome_options)

try:
    # Visitar o site
    driver.get("https://cliente01.sinapseinformatica.com.br:4383/AGE/Web/Aniel.Connect/?IdAcesso=18560#")

    # Digitar Usuário e Senha
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#UserName'))).send_keys('9999')
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#Password'))).send_keys('Age2023#')

    # Clicar no botão de login
    driver.find_element(By.CSS_SELECTOR, '.login100-form-btn').click()

    # Visitar a próxima página
    driver.get("https://cliente01.sinapseinformatica.com.br:4383/AGE/Web/Aniel.Connect/pt-BR/Acompanhamento_Servico?CodCt=OP01&Projeto=CASA%20CLIENTE&Num_Obra=1186968&Num_Doc=1186968#!")

    # Clicar na aba do técnico
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#tecnico-tab'))).click()

    # Esperar pelo iframe
    WebDriverWait(driver, 10).until(EC.frame_to_be_available_and_switch_to_it((By.CSS_SELECTOR, 'iframe[src="/AGE/Web/Aniel.Connect/pt-BR/Acompanhamento_Servico/Tecnico?Cod_Ct=OP01&Projeto=CASA CLIENTE&Num_Obra=1186968&Num_Doc=1186968"]')))

    # Esperar pelos elementos dentro do iframe e interagir
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '.card')))
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#accordion')))
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '.card-body')))

    # Verificar texto e clicar
    assert "Trocar Responsável da OS" in driver.page_source
    driver.find_element(By.XPATH, '/html/body/form/div[1]/div/div[1]/div/div/div/div/div[2]/a').click()

    # Voltar do iframe
    driver.switch_to.default_content()

    # Clicar no botão de salvar flutuante
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '.float-save'))).click()

    # Pausar por 1 segundo
    WebDriverWait(driver, 1).until(EC.alert_is_present())

finally:
    # Fechar o navegador
    driver.quit()
