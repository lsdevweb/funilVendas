// Update progress to 100%                document.getElementById('progress-text').textContent = 'Diagnóstico Completo';
                document.getElementById('progress-percentage').textContent = '100%';
                document.getElementById('progress-bar').style.width = '100%';
            });
            
            // Add click event to restart button <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables
            let currentStep = 1;
            const totalSteps = 6;
            let companyProfile = {};
            let scores = {
                fluxo_caixa: 0,
                planejamento: 0,
                precificacao: 0,
                custos: 0,
                investimentos: 0
            };
            let questionCounts = {
                fluxo_caixa: 0,
                planejamento: 0,
                precificacao: 0,
                custos: 0,
                investimentos: 0
            };
            
            // Select all option cards
            const optionCards = document.querySelectorAll('.option-card');
            
            // Add click event to option cards
            optionCards.forEach(card => {
                card.addEventListener('click', function() {
                    // If this is in step 1, handle differently (can select multiple)
                    if (currentStep === 1) {
                        const parentDiv = this.parentElement;
                        const siblings = parentDiv.querySelectorAll('.option-card');
                        
                        siblings.forEach(sibling => {
                            sibling.classList.remove('selected');
                        });
                        
                        this.classList.add('selected');
                    } else {
                        // For other steps, add points to category
                        const category = this.dataset.category;
                        const points = parseInt(this.dataset.points);
                        
                        // Remove selected class from siblings
                        const parentDiv = this.parentElement;
                        const siblings = parentDiv.querySelectorAll('.option-card');
                        
                        siblings.forEach(sibling => {
                            sibling.classList.remove('selected');
                        });
                        
                        // Add selected class to this card
                        this.classList.add('selected');
                        
                        // Update scores
                        if (category && points) {
                            scores[category] += points;
                            questionCounts[category]++;
                        }
                    }
                });
            });
            
            // Navigation buttons
            const nextButtons = document.querySelectorAll('.next-btn');
            const prevButtons = document.querySelectorAll('.prev-btn');
            const finishButton = document.getElementById('finish-btn');
            const restartButton = document.getElementById('restart-btn');
            const downloadButton = document.getElementById('download-btn');
            
            // Add click event to next buttons
            nextButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // If we're on step 1, save company profile
                    if (currentStep === 1) {
                        const selectedCards = document.querySelectorAll('#step-1 .option-card.selected');
                        selectedCards.forEach(card => {
                            const parentLabel = card.parentElement.previousElementSibling.textContent;
                            companyProfile[parentLabel] = card.dataset.value;
                        });
                    }
                    
                    // Hide current step
                    document.getElementById(`step-${currentStep}`).classList.add('hidden');
                    
                    // Show next step
                    currentStep++;
                    document.getElementById(`step-${currentStep}`).classList.remove('hidden');
                    
                    // Update progress
                    updateProgress();
                });
            });
            
            // Add click event to previous buttons
            prevButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Hide current step
                    document.getElementById(`step-${currentStep}`).classList.add('hidden');
                    
                    // Show previous step
                    currentStep--;
                    document.getElementById(`step-${currentStep}`).classList.remove('hidden');
                    
                    // Update progress
                    updateProgress();
                });
            });
            
            // Add click event to finish button
            finishButton.addEventListener('click', function() {
                // Hide current step
                document.getElementById(`step-${currentStep}`).classList.add('hidden');
                
                // Calculate final scores
                calculateFinalScores();
                
                // Show results
                document.getElementById('results').classList.remove('hidden');
                
            restartButton.addEventListener('click', function() {
                // Hide results
                document.getElementById('results').classList.add('hidden');
                
                // Reset scores
                scores = {
                    fluxo_caixa: 0,
                    planejamento: 0,
                    precificacao: 0,
                    custos: 0,
                    investimentos: 0
                };
                
                questionCounts = {
                    fluxo_caixa: 0,
                    planejamento: 0,
                    precificacao: 0,
                    custos: 0,
                    investimentos: 0
                };
                
                // Reset selected options
                optionCards.forEach(card => {
                    card.classList.remove('selected');
                });
                
                // Show first step
                currentStep = 1;
                document.getElementById('step-1').classList.remove('hidden');
                
                // Update progress
                updateProgress();
            });
            
            // Add click event to download button (demo)
            downloadButton.addEventListener('click', function() {
                alert('Em um ambiente real, esta função geraria um PDF com os resultados detalhados do diagnóstico financeiro.');
            });
            
            // Update progress bar
            function updateProgress() {
                const progress = ((currentStep - 1) / totalSteps) * 100;
                document.getElementById('progress-text').textContent = `Etapa ${currentStep} de ${totalSteps}`;
                document.getElementById('progress-percentage').textContent = `${Math.round(progress)}%`;
                document.getElementById('progress-bar').style.width = `${progress}%`;
            }
            
            // Calculate final scores
            function calculateFinalScores() {
                // Calculate percentage scores for each category
                const categoryScores = {};
                let totalScore = 0;
                let totalQuestions = 0;
                
                for (const category in scores) {
                    const maxPossible = questionCounts[category] * 4; // 4 is max points per question
                    if (maxPossible > 0) {
                        const percentage = (scores[category] / maxPossible) * 100;
                        categoryScores[category] = Math.round(percentage);
                        
                        totalScore += scores[category];
                        totalQuestions += questionCounts[category];
                    } else {
                        categoryScores[category] = 0;
                    }
                }
                
                // Calculate overall score
                const overallPercentage = totalQuestions > 0 ? Math.round((totalScore / (totalQuestions * 4)) * 100) : 0;
                
                // Update score displays
                document.getElementById('overall-score-bar').style.width = `${overallPercentage}%`;
                document.getElementById('overall-score-text').textContent = `${overallPercentage}% - ${getScoreLevel(overallPercentage)}`;
                
                for (const category in categoryScores) {
                    document.getElementById(`${category}-score-bar`).style.width = `${categoryScores[category]}%`;
                    document.getElementById(`${category}-score`).textContent = `${categoryScores[category]}%`;
                    document.getElementById(`${category}-feedback`).innerHTML = getCategoryFeedback(category, categoryScores[category]);
                }
                
                // Generate recommendations
                document.getElementById('recommendations').innerHTML = generateRecommendations(categoryScores);
            }
            
            // Get score level description
            function getScoreLevel(score) {
                if (score < 25) return "Iniciante";
                if (score < 50) return "Básico";
                if (score < 75) return "Intermediário";
                return "Avançado";
            }
            
            // Get category feedback
            function getCategoryFeedback(category, score) {
                const feedbacks = {
                    fluxo_caixa: {
                        low: "Seu controle de fluxo de caixa precisa de atenção imediata. A falta de monitoramento pode levar a problemas de liquidez.",
                        medium: "Você tem um controle básico do fluxo de caixa, mas há espaço para melhorias na frequência e detalhamento.",
                        high: "Seu controle de fluxo de caixa é bom, com monitoramento regular e projeções."
                    },
                    planejamento: {
                        low: "Seu planejamento financeiro é mínimo ou inexistente, o que aumenta riscos e limita o crescimento.",
                        medium: "Você tem elementos básicos de planejamento, mas falta estrutura e visão de longo prazo.",
                        high: "Seu planejamento financeiro é estruturado, com orçamentos e separação clara entre finanças pessoais e do negócio."
                    },
                    precificacao: {
                        low: "Sua estratégia de precificação é intuitiva, sem considerar todos os custos e o valor real do seu produto/serviço.",
                        medium: "Você considera custos básicos na precificação, mas pode estar deixando de maximizar margens.",
                        high: "Sua abordagem de precificação é estratégica, considerando custos, valor e mercado."
                    },
                    custos: {
                        low: "Sua gestão de custos é informal, sem controle detalhado ou análises regulares.",
                        medium: "Você monitora custos básicos, mas falta análise aprofundada e estratégias de redução.",
                        high: "Sua gestão de custos é estruturada, com categorização, análises e estratégias de otimização."
                    },
                    investimentos: {
                        low: "Suas decisões de investimento são intuitivas, sem análise formal de retorno ou planejamento de longo prazo.",
                        medium: "Você considera aspectos básicos nos investimentos, mas falta uma estratégia estruturada de crescimento.",
                        high: "Você tem uma abordagem estratégica para investimentos, com análises de retorno e plano de longo prazo."
                    }
                };
                
                if (score < 40) return feedbacks[category].low;
                if (score < 70) return feedbacks[category].medium;
                return feedbacks[category].high;
            }
            
            // Generate recommendations
            function generateRecommendations(scores) {
                let recommendations = [];
                
                // Fluxo de Caixa
                if (scores.fluxo_caixa < 40) {
                    recommendations.push("<p><strong>Fluxo de Caixa:</strong> Implemente um sistema básico de registro diário de entradas e saídas. Use uma planilha simples ou um aplicativo gratuito para começar.</p>");
                } else if (scores.fluxo_caixa < 70) {
                    recommendations.push("<p><strong>Fluxo de Caixa:</strong> Estruture melhor seu controle de fluxo de caixa com categorias claras e faça projeções para pelo menos 3 meses à frente.</p>");
                } else {
                    recommendations.push("<p><strong>Fluxo de Caixa:</strong> Continue com seu bom controle e considere ferramentas mais avançadas de análise para identificar tendências e oportunidades de otimização.</p>");
                }
                
                // Planejamento
                if (scores.planejamento < 40) {
                    recommendations.push("<p><strong>Planejamento Financeiro:</strong> Crie um orçamento básico mensal e separe contas pessoais das do negócio. Comece a construir uma pequena reserva de emergência.</p>");
                } else if (scores.planejamento < 70) {
                    recommendations.push("<p><strong>Planejamento Financeiro:</strong> Desenvolva um orçamento anual com metas específicas e aumente sua reserva de emergência para pelo menos 3 meses de despesas.</p>");
                } else {
                    recommendations.push("<p><strong>Planejamento Financeiro:</strong> Refine seu planejamento com cenários alternativos e integre-o ao seu plano estratégico de negócios.</p>");
                }
                
                // Precificação
                if (scores.precificacao < 40) {
                    recommendations.push("<p><strong>Precificação:</strong> Revise sua estrutura de custos e calcule o custo real de cada produto/serviço, incluindo custos indiretos. Estabeleça margens mínimas.</p>");
                } else if (scores.precificacao < 70) {
                    recommendations.push("<p><strong>Precificação:</strong> Analise a rentabilidade de cada produto/serviço e considere estratégias de precificação baseadas em valor, não apenas em custos.</p>");
                } else {
                    recommendations.push("<p><strong>Precificação:</strong> Implemente testes de preço e análise de elasticidade para otimizar ainda mais suas margens e posicionamento no mercado.</p>");
                }
                
                // Custos
                if (scores.custos < 40) {
                    recommendations.push("<p><strong>Gestão de Custos:</strong> Crie um sistema de categorização de custos e calcule seu ponto de equilíbrio. Identifique os 3 maiores custos e busque alternativas.</p>");
                } else if (scores.custos < 70) {
                    recommendations.push("<p><strong>Gestão de Custos:</strong> Estabeleça metas de<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9468488c44e86449',t:'MTc0ODM3Nzk2NS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();
