from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
import sys
import time

if len(sys.argv) > 2:
    param1 = sys.argv[1]
    param2 = sys.argv[2]
    param3 = sys.argv[3]
    param4 = sys.argv[4]

else:
    print("Nenhum parâmetro passado.")

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
    driver.get(f"https://cliente01.sinapseinformatica.com.br:4383/AGE/Web/Aniel.Connect/pt-BR/Acompanhamento_Servico?CodCt=OP01&Projeto=CASA%20CLIENTE&Num_Obra={param1}&Num_Doc={param1}#!")

    # Esperar pelo iframe
    WebDriverWait(driver, 10).until(EC.frame_to_be_available_and_switch_to_it((By.CSS_SELECTOR, 'iframe#iDadosGeraisFrame')))


    # Clicar na data de agendamento
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#idataAgendamento'))).click()
    time.sleep(1)

    element = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#idataAgendamento')))

    actions = ActionChains(driver)
    actions.click(element)  # Focar no elemento
    actions.key_down(Keys.CONTROL).send_keys('a').key_up(Keys.CONTROL)
    actions.send_keys(Keys.BACKSPACE)  # Pressionar Backspace

    # Executar as ações
    actions.perform()
    time.sleep(1)

    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#idataAgendamento'))).send_keys(param2)
    time.sleep(1)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '.selection'))).click()
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '.select2-search__field'))).click()
    time.sleep(1)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '.select2-search__field'))).send_keys(param3)
    time.sleep(1)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '.select2-search__field'))).send_keys(Keys.ENTER)
    time.sleep(1)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#btnAdicionar'))).click()
    time.sleep(1)
    driver.switch_to.default_content()
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '.swal2-textarea'))).click()
    time.sleep(1)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '.swal2-textarea'))).send_keys(param4)
    time.sleep(1)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, 'button.swal2-confirm'))).click()
    driver.switch_to.default_content()
    WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.CSS_SELECTOR, 'a.float-save.floatButton'))).click()
    driver.execute_script("window.scrollTo(0, 0);")
    time.sleep(3)
    WebDriverWait(driver, 10).until(EC.frame_to_be_available_and_switch_to_it((By.CSS_SELECTOR, 'iframe#iDadosGeraisFrame')))
    time.sleep(3)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#select2-iMotivo_ReagendamentoM-container'))).click()
    time.sleep(1)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, 'input[type=search].select2-search__field'))).click()
    time.sleep(1)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, 'input[type=search].select2-search__field'))).send_keys('Outro')
    time.sleep(1)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, 'input[type=search].select2-search__field'))).send_keys(Keys.ENTER)
    time.sleep(1)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#iMotivo_Reagendamento_Outros'))).click()
    time.sleep(1)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#iMotivo_Reagendamento_Outros'))).send_keys(param4)
    time.sleep(1)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#iEditarAtividades'))).click()
    time.sleep(1)
    driver.switch_to.default_content()
    # Clicar no botão de salvar flutuante
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, 'a.float-save.floatButton'))).click()
    time.sleep(3)


    # Clicar na aba do técnico
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#tecnico-tab'))).click()
    time.sleep(3)

    # Esperar pelo iframe
    WebDriverWait(driver, 10).until(EC.frame_to_be_available_and_switch_to_it((By.CSS_SELECTOR, 'iframe#iTecnicoFrame')))
    time.sleep(3)

    # Esperar pelos elementos dentro do iframe e interagir
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '.card')))
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#accordion')))
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '.card-body')))

    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#AlterarCampo'))).click()
    time.sleep(3)

    # Voltar do iframe
    driver.switch_to.default_content()
    time.sleep(1)

    # Clicar no botão de salvar flutuante
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, 'a.float-save.floatButton'))).click()
    time.sleep(5)
    print('true')

finally:
    # Fechar o navegador
    driver.quit()
